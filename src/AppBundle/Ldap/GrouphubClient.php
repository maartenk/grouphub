<?php

namespace AppBundle\Ldap;

use AppBundle\Model\Group;
use AppBundle\Model\User;
use AppBundle\Sequence;
use AppBundle\SynchronizableSequence;
use Doctrine\Common\Comparable;
use InvalidArgumentException;

/**
 * Class GrouphubClient
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class GrouphubClient
{
    /**
     * @var LdapClient
     */
    private $ldap;

    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @var string[]
     */
    private $usersDn;

    /**
     * @var string[]
     */
    private $groupsDn;

    /**
     * @var string
     */
    private $grouphubDn;

    /**
     * @var string
     */
    private $formalDn;

    /**
     * @var string
     */
    private $adhocDn;

    /**
     * @var string
     */
    private $adminGroupsDn;

    /**
     * @var string
     */
    private $userQuery;

    /**
     * @var string
     */
    private $groupQuery;

    /**
     * @param LdapClient $ldap
     * @param Normalizer $normalizer
     * @param string[]   $usersDn
     * @param string[]   $groupsDn
     * @param string     $grouphubDn
     * @param string     $formalDn
     * @param string     $adhocDn
     * @param string     $adminGroupsDn
     * @param string     $userQuery
     * @param string     $groupQuery
     */
    public function __construct(
        LdapClient $ldap,
        $normalizer,
        array $usersDn,
        array $groupsDn,
        $grouphubDn,
        $formalDn,
        $adhocDn,
        $adminGroupsDn = '',
        $userQuery = 'cn=*',
        $groupQuery = 'cn=*'
    ) {
        $this->ldap = $ldap;
        $this->normalizer = $normalizer;

        $this->usersDn = $usersDn;
        $this->groupsDn = $groupsDn;
        $this->grouphubDn = $grouphubDn;
        $this->formalDn = $formalDn;
        $this->adhocDn = $adhocDn;
        $this->adminGroupsDn = $adminGroupsDn;
        $this->userQuery = $userQuery;
        $this->groupQuery = $groupQuery;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return Sequence|User[]
     */
    public function findUsers($offset, $limit)
    {
        return $this->findEntities($this->usersDn, $this->userQuery, ['*'], $offset, $limit, function ($data) {
            return $this->normalizer->denormalizeUsers($data);
        });
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return Sequence|Group[]
     */
    public function findGroups($offset, $limit)
    {
        return $this->findEntities($this->groupsDn, $this->groupQuery, ['*'], $offset, $limit, function ($data) {
            return $this->normalizer->denormalizeGroups($data);
        });
    }

    /**
     * @param array    $dns
     * @param string   $query
     * @param array    $filter
     * @param int      $offset
     * @param int      $limit
     * @param \Closure $normalizer
     *
     * @return Sequence
     */
    private function findEntities(array $dns, $query, $filter, $offset, $limit, \Closure $normalizer)
    {
        $entities = [];

        foreach ($dns as $dn) {
            $data = $this->ldap->find($dn, $query, $filter, '');

            if (empty($data)) {
                continue;
            }

            $entities = array_merge($entities, $normalizer($data));
        }

        if (count($dns) > 1) {
            usort(
                $entities,
                function (Comparable $a, Comparable $b) {
                    return $a->compareTo($b);
                }
            );
        }

        // @todo: use actual offset/limit
        $entities = array_slice($entities, $offset, $limit);

        return new Sequence($entities);
    }

    /**
     * @param string $groupReference
     * @param int    $offset
     * @param int    $limit
     *
     * @return SynchronizableSequence
     */
    public function findGroupUsers($groupReference, $offset, $limit)
    {
        $data = $this->ldap->find($groupReference, 'cn=*', ['member'], null, $offset, $limit);

        if (empty($data)) {
            return new SynchronizableSequence([]);
        }

        $users = $this->normalizer->denormalizeGroupUsers($data);

        // @todo: use actual offset/limit
        $users = array_slice($users, $offset, $limit);

        return new SynchronizableSequence($users);
    }

    /**
     * @param Group $group
     * @param int   $offset
     * @param int   $limit
     *
     * @return SynchronizableSequence
     */
    public function findGroupAdmins(Group $group, $offset, $limit)
    {
        return $this->findGroupUsers($this->getAdminGroupReference($group), $offset, $limit);
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return SynchronizableSequence
     */
    public function findGrouphubGroups($offset, $limit)
    {
        $data = $this->ldap->find($this->grouphubDn, 'cn=*', ['*'], '', $offset, $limit);

        if (empty($data)) {
            return new SynchronizableSequence([]);
        }

        $groups = $this->normalizer->denormalizeGrouphubGroups($data);

        // @todo: use actual offset/limit
        $groups = array_slice($groups, $offset, $limit);

        return new SynchronizableSequence($groups);
    }

    /**
     * @param array $groupIds
     *
     * @return SynchronizableSequence
     */
    public function findGrouphubGroupsByIds(array $groupIds = [])
    {
        if (empty($groupIds)) {
            return new SynchronizableSequence([]);
        }

        $query = '(|(cn=*:' . implode(')(cn=*:', $groupIds) . '))';

        $data = $this->ldap->find($this->grouphubDn, $query, ['*'], '');

        if (empty($data)) {
            return new SynchronizableSequence([]);
        }

        $groups = $this->normalizer->denormalizeGrouphubGroups($data);

        return new SynchronizableSequence($groups);
    }

    /**
     * @param Group $group
     * @param bool  $syncAdminGroup
     *
     * @return Group
     */
    public function addGroup(Group $group, $syncAdminGroup = false)
    {
        $cn = $group->getName() . ':' . $group->getId();

        $dn = null;
        switch ($group->getType()) {
            case Group::TYPE_FORMAL:
                $dn = $this->formalDn;
                break;
            case Group::TYPE_GROUPHUB:
                $dn = $this->adhocDn;
                break;
            default:
                throw new InvalidArgumentException('Invalid group');
        }

        $dn = 'cn=' . strtolower($cn) . ',' . $dn;

        $group->setReference($dn);

        $data = $this->normalizer->normalizeGroup($group);

        $this->ldap->add($group->getReference(), $data);

        if ($syncAdminGroup) {
            $this->addAdminGroupIfNotExists($group);
        }

        return $group;
    }

    /**
     * @param Group $group
     */
    public function addAdminGroupIfNotExists(Group $group)
    {
        $data = $this->normalizer->normalizeGroup($group);

        try {
            $this->ldap->add($this->getAdminGroupReference($group), $data);
        } catch (\Exception $e) {
            if (stripos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
    }

    /**
     * @param Group $oldGroup
     * @param Group $newGroup
     * @param bool  $syncAdminGroup
     */
    public function updateGroup(Group $oldGroup, Group $newGroup, $syncAdminGroup = false)
    {
        $groupReference = $oldGroup->getReference();

        $data = $this->normalizer->normalizeGroupForUpdate($newGroup);

        $this->ldap->deleteAttribute($groupReference, ['cn' => $oldGroup->getName()]);
        $this->ldap->addAttribute($groupReference, ['cn' => $newGroup->getName()]);
        $this->ldap->modify($groupReference, $data);

        if ($syncAdminGroup) {
            $this->ldap->modify($this->getAdminGroupReference($oldGroup), $data);
        }
    }

    /**
     * @param Group $group
     * @param bool  $syncAdminGroup
     */
    public function removeGroup(Group $group, $syncAdminGroup = false)
    {
        $this->ldap->delete($group->getReference());

        if ($syncAdminGroup) {
            $this->ldap->delete($this->getAdminGroupReference($group));
        }
    }

    /**
     * @param string $groupReference
     * @param string $userReference
     */
    public function addGroupUser($groupReference, $userReference)
    {
        $this->ldap->addAttribute($groupReference, ['member' => $userReference]);
    }

    /**
     * @param Group  $group
     * @param string $userReference
     */
    public function addGroupAdmin(Group $group, $userReference)
    {
        $this->addGroupUser($this->getAdminGroupReference($group), $userReference);
    }

    /**
     * @param string $groupReference
     * @param string $userReference
     */
    public function removeGroupUser($groupReference, $userReference)
    {
        $this->ldap->deleteAttribute($groupReference, ['member' => $userReference]);
    }

    /**
     * @param Group  $group
     * @param string $userReference
     */
    public function removeGroupAdmin(Group $group, $userReference)
    {
        $this->removeGroupUser($this->getAdminGroupReference($group), $userReference);
    }

    /**
     * @param Group $group
     *
     * @return string
     */
    private function getAdminGroupReference(Group $group)
    {
        $pos = strpos($group->getReference(), ',');

        return substr($group->getReference(), 0, $pos) . ':admins,' . $this->adminGroupsDn;
    }
}
