<?php

namespace AppBundle\Ldap;

use AppBundle\Model\Group;
use AppBundle\Sequence;
use AppBundle\SynchronizableSequence;
use InvalidArgumentException;

/**
 * Class GrouphubClient
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
     * @var string
     */
    private $usersDn;

    /**
     * @var string
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
     * @param LdapClient $ldap
     * @param Normalizer $normalizer
     * @param string     $usersDn
     * @param string     $groupsDn
     * @param string     $grouphubDn
     * @param string     $formalDn
     * @param string     $adhocDn
     * @param string     $adminGroupsDn
     */
    public function __construct(LdapClient $ldap, $normalizer, $usersDn, $groupsDn, $grouphubDn, $formalDn, $adhocDn, $adminGroupsDn = '')
    {
        $this->ldap = $ldap;
        $this->normalizer = $normalizer;

        $this->usersDn = $usersDn;
        $this->groupsDn = $groupsDn;
        $this->grouphubDn = $grouphubDn;
        $this->formalDn = $formalDn;
        $this->adhocDn = $adhocDn;
        $this->adminGroupsDn = $adminGroupsDn;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return Sequence
     */
    public function findUsers($offset, $limit)
    {
        $data = $this->ldap->find($this->usersDn, 'cn=*', '*', '', $offset, $limit);

        if (empty($data)) {
            return new Sequence([]);
        }

        $users = $this->normalizer->denormalizeUsers($data);

        // @todo: use actual offset/limit
        $users = array_slice($users, $offset, $limit);

        return new Sequence($users);
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return Sequence
     */
    public function findGroups($offset, $limit)
    {
        $data = $this->ldap->find($this->groupsDn, 'cn=*', ['cn', 'description'], '', $offset, $limit);

        if (empty($data)) {
            return new Sequence([]);
        }

        $groups = $this->normalizer->denormalizeGroups($data);

        // @todo: use actual offset/limit
        $groups = array_slice($groups, $offset, $limit);

        return new Sequence($groups);
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
        $data = $this->ldap->find($this->grouphubDn, 'cn=*', ['cn'], '', $offset, $limit);

        if (empty($data)) {
            return new SynchronizableSequence([]);
        }

        $groups = $this->normalizer->denormalizeGrouphubGroups($data);

        // @todo: use actual offset/limit
        $groups = array_slice($groups, $offset, $limit);

        return new SynchronizableSequence($groups);
    }

    /**
     * @param Group $group
     *
     * @return Group
     */
    public function addGroup(Group $group)
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

        $dn = 'cn=' . $cn . ',' . $dn;

        $group->setReference($dn);

        $data = $this->normalizer->normalizeGroup($group);

        $this->ldap->add($group->getReference(), $data);

        if ($this->adminGroupsDn) {
            $this->ldap->add($this->getAdminGroupReference($group), $data);
        }

        return $group;
    }

    /**
     * @param string $groupReference
     * @param Group  $group
     */
    public function updateGroup($groupReference, Group $group)
    {
        // Not supported...
    }

    /**
     * @param Group $group
     */
    public function removeGroup(Group $group)
    {
        $this->ldap->delete($group->getReference());

        if ($this->adminGroupsDn) {
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
        if ($this->adminGroupsDn) {
            $this->addGroupUser($this->getAdminGroupReference($group), $userReference);
        }
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
        if ($this->adminGroupsDn) {
            $this->removeGroupUser($this->getAdminGroupReference($group), $userReference);
        }
    }

    /**
     * @param Group $group
     *
     * @return string
     */
    public function getAdminGroupReference(Group $group)
    {
        $cn = $group->getName() . ':' . $group->getId();

        return 'cn=' . $cn . ':admins,' . $this->adminGroupsDn;
    }
}
