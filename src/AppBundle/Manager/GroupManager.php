<?php

namespace AppBundle\Manager;

use AppBundle\Api\ApiClient;
use AppBundle\Ldap\GrouphubClient;
use AppBundle\Model\Group;
use AppBundle\Model\Membership;
use AppBundle\Model\User;

/**
 * Class GroupManager
 */
class GroupManager
{
    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @var GrouphubClient
     */
    private $ldapClient;

    /***
     * @param ApiClient      $client
     * @param GrouphubClient $ldapClient
     */
    public function __construct(ApiClient $client, GrouphubClient $ldapClient)
    {
        $this->client = $client;
        $this->ldapClient = $ldapClient;
    }

    /**
     * @param User $user
     *
     * @return array
     *
     * @todo: revise into more solid caching..
     */
    public function getMyGroups(User $user)
    {
        static $cache;

        if (isset($cache[$user->getId()])) {
            return $cache[$user->getId()];
        }

        /** @var Membership[] $memberships */
        $memberships = $this->client->findUserMemberships($user->getId());

        // Regroup memberships to make them a little more accessible
        $groups = [];
        foreach ($memberships as $group) {
            $type = $group->getGroup()->getType() === 'grouphub' ? 'grouphub' : 'other';
            $role = $group->getRole();

            $groups[$type][$role][$group->getGroup()->getId()] = $group->getGroup();
        }

        return $cache[$user->getId()] = $groups;
    }

    /**
     * @param string $query
     * @param string $type
     * @param int    $offset
     * @param int    $limit
     *
     * @return Group[]
     */
    public function findGroups($query = null, $type = null, $offset = 0, $limit = 100)
    {
        return $this->client->findGroups($query, $type, $offset, $limit);
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return Group[]
     */
    public function findFormalGroups($offset = 0, $limit = 100)
    {
        return $this->client->findFormalGroups($offset, $limit);
    }

    /**
     * @param int $id
     *
     * @return Group
     */
    public function getGroup($id)
    {
        return $this->client->getGroup($id);
    }

    /**
     * @param int    $id
     * @param string $query
     * @param int    $offset
     * @param int    $limit
     *
     * @return Membership[]
     */
    public function findGroupMemberships($id, $query = null, $offset = 0, $limit = 100)
    {
        return $this->client->findGroupMemberships($id, $query, $offset, $limit);
    }

    /**
     * @param int $groupId
     * @param int $userId
     *
     * @return Membership
     * @todo: a user could have multiple memberships
     */
    public function findUserMembershipOfGroup($groupId, $userId)
    {
        return $this->client->findUserMembershipOfGroup($userId, $groupId);
    }

    /**
     * @param int    $groupId
     * @param int    $userId
     * @param string $role
     */
    public function updateMembership($groupId, $userId, $role)
    {
        $this->client->updateGroupUser($groupId, $userId, $role);
    }

    /**
     * @param int $groupId
     * @param int $userId
     */
    public function deleteMembership($groupId, $userId)
    {
        $this->client->removeGroupUser($groupId, $userId);
    }

    /**
     * @param int $groupId
     * @param int $userId
     */
    public function addMembership($groupId, $userId)
    {
        $this->client->addGroupUser($groupId, $userId);
    }

    /**
     * @param int $id
     */
    public function deleteGroup($id)
    {
        $this->client->removeGroup($id);
    }

    /**
     * @param Group $group
     */
    public function addGroup(Group $group)
    {
        $group = $this->client->addGroup($group);

        $this->ldapClient->addGroup($group);
        $this->client->updateGroupReference($group->getId(), $group->getReference());
    }

    /**
     * @param string $query
     * @param int    $offset
     * @param int    $limit
     *
     * @return User[]
     */
    public function findUsers($query = null, $offset = 0, $limit = 100)
    {
        return $this->client->findUsers($query, $offset, $limit);
    }

    /**
     * @param string $loginName
     *
     * @return User
     */
    public function getUserByLoginName($loginName)
    {
        return $this->client->getUserByLoginName($loginName);
    }

    /**
     * @param int $userId
     *
     * @return Membership[]
     */
    public function findUserMemberships($userId)
    {
        return $this->client->findUserMemberships($userId);
    }
}
