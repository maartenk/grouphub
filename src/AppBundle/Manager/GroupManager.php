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
     * @param User   $user
     * @param string $sortColumn
     * @param int    $sortDirection
     *
     * @return array
     * @todo: integrate better way of caching
     * @todo: find a way to use offset/limit
     */
    public function getMyGroups(User $user, $sortColumn = 'name', $sortDirection = 0)
    {
        static $cache;

        $key = md5(json_encode([$user->getId(), $sortColumn, $sortDirection]));

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        /** @var Membership[] $memberships */
        $memberships = $this->client->findUserMemberships($user->getId(), $sortColumn, $sortDirection);

        // Regroup memberships to make them a little more accessible
        $groups = [];
        foreach ($memberships as $group) {
            $type = $group->getGroup()->getType() === 'grouphub' ? 'grouphub' : 'other';
            $role = $group->getRole();

            $groups[$type][$role][$group->getGroup()->getId()] = $group->getGroup();
        }

        $cache[$key] = $groups;

        return $groups;
    }

    /**
     * @param string $query
     * @param string $type
     * @param int    $offset
     * @param int    $limit
     * @param string $sortColumn
     * @param int    $sortDirection
     *
     * @return Group[]
     */
    public function findGroups(
        $query = null,
        $type = null,
        $offset = 0,
        $limit = 100,
        $sortColumn = 'name',
        $sortDirection = 0
    ) {
        return $this->client->findGroups($query, $type, $offset, $limit, $sortColumn, $sortDirection);
    }

    /**
     * @param int $userId
     *
     * @return Group[]
     */
    public function findFormalGroups($userId)
    {
        /** @var Membership[] $memberships */
        $memberships = $this->client->findUserMemberships($userId, 'name', 0, 'formal');

        $result = [];
        foreach ($memberships as $membership) {
            $group = $membership->getGroup();

            if (empty($group->getParentId()) || $group->getParentId() === 1) {
                $result[] = $group;
            }
        }

        return $result;
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
        $this->client->addGroup($group);
    }

    /**
     * @param Group $group
     */
    public function updateGroup(Group $group)
    {
        $this->client->updateGroup($group->getId(), $group);
    }
}
