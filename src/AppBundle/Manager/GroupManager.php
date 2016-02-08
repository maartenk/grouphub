<?php

namespace AppBundle\Manager;

use AppBundle\Api\ApiClient;
use AppBundle\Model\Group;
use AppBundle\Model\Membership;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @param UserInterface $user
     *
     * @return array
     */
    public function getMyGroups(UserInterface $user)
    {
        /** @var Membership[] $memberships */
        $memberships = $this->client->findUserMemberships(7); // @todo: $user->getId();

        // Regroup memberships to make them a little more accessible
        $groups = [];
        foreach ($memberships as $group) {
            $type = $group->getGroup()->getType() === 'grouphub' ? 'grouphub' : 'other';
            $role = $group->getRole();

            $groups[$type][$role][$group->getGroup()->getId()] = $group->getGroup();
        }

        return $groups;
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
}
