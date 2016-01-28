<?php

namespace AppBundle\Ldap;

use AppBundle\Model\Group;
use AppBundle\Sequence;
use AppBundle\SynchronizableSequence;

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
     * @param LdapClient $ldap
     * @param Normalizer $normalizer
     * @param string     $usersDn
     * @param string     $groupsDn
     * @param string     $grouphubDn
     */
    public function __construct(LdapClient $ldap, $normalizer, $usersDn, $groupsDn, $grouphubDn)
    {
        $this->ldap = $ldap;
        $this->normalizer = $normalizer;

        $this->usersDn = $usersDn;
        $this->groupsDn = $groupsDn;
        $this->grouphubDn = $grouphubDn;
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
        $data = $this->ldap->find($this->groupsDn, 'cn=*', ['cn'], '', $offset, $limit);

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
        $dn = 'cn=' . $group->getName() . ',' . $this->grouphubDn;
        $group->setReference($dn);

        $data = $this->normalizer->normalizeGroup($group);

        $this->ldap->add($group->getReference(), $data);

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
     * @param string $groupReference
     */
    public function removeGroup($groupReference)
    {
        $this->ldap->delete($groupReference);
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
     * @param string $groupReference
     * @param string $userReference
     */
    public function removeGroupUser($groupReference, $userReference)
    {
        $this->ldap->deleteAttribute($groupReference, ['member' => $userReference]);
    }
}
