<?php

namespace AppBundle\Manager;

use AppBundle\Api\ApiClient;
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

    /***
     * @param ApiClient $client
     */
    public function __construct(ApiClient $client)
    {
        $this->client = $client;
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
     * @param int $id
     *
     * @return Membership[]
     */
    public function findGroupMemberships($id)
    {
        return $this->client->findGroupMemberships($id);
    }

    /**
     * @param int $groupId
     * @param int $userId
     *
     * @return Membership
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
}
