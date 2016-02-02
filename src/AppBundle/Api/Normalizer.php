<?php

namespace AppBundle\Api;

use AppBundle\Model\Group;
use AppBundle\Model\Membership;
use AppBundle\Model\User;
use DateTime;

/**
 * Class Normalizer
 */
class Normalizer
{
    /**
     * @param User $user
     *
     * @return array
     */
    public function normalizeUser(User $user)
    {
        return [
            'reference' => $user->getReference(),
            'firstName' => $user->getFirstName(),
            'lastName'  => $user->getLastName(),
            'loginName' => $user->getLoginName(),
        ];
    }

    /**
     * @param array $users
     *
     * @return User[]
     */
    public function denormalizeUsers(array $users)
    {
        $result = [];
        foreach ($users as $user) {
            $result[] = $this->denormalizeUser($user);
        }

        return $result;
    }

    /**
     * @param array $users
     *
     * @return User[]
     */
    public function denormalizeGroupUsers(array $users)
    {
        $result = [];
        foreach ($users as $user) {
            $result[] = $this->denormalizeUser($user['user']);
        }

        return $result;
    }

    /**
     * @param array $user
     *
     * @return User
     */
    public function denormalizeUser(array $user)
    {
        return new User(
            $user['id'],
            $user['reference'],
            isset($user['first_name']) ? $user['first_name'] : '',
            isset($user['last_name']) ? $user['last_name'] : '',
            isset($user['login_name']) ? $user['login_name'] : ''
        );
    }

    /**
     * @param Group $group
     *
     * @return array
     */
    public function normalizeGroup(Group $group)
    {
        return [
            'reference'   => $group->getReference(),
            'name'        => $group->getName(),
            'description' => $group->getDescription(),
            'type'        => $group->getType(),
            'owner'       => $group->getOwnerId(),
            'parent'      => $group->getParentId(),
        ];
    }

    /**
     * @param array $groups
     *
     * @return Group[]
     */
    public function denormalizeGroups(array $groups)
    {
        $result = [];
        foreach ($groups as $group) {
            $result[] = $this->denormalizeGroup($group);
        }

        return $result;
    }

    /**
     * @param array $group
     *
     * @return Group
     */
    public function denormalizeGroup(array $group)
    {
        return new Group(
            $group['id'],
            $group['reference'],
            isset($group['name']) ? $group['name'] : '',
            isset($group['description']) ? $group['description'] : '',
            isset($group['type']) ? $group['type'] : '',
            isset($group['owner']['id']) ? $group['owner']['id'] : null,
            isset($group['parent']['id']) ? $group['parent']['id'] : null,
            isset($group['timestamp']) ? new DateTime($group['timestamp']) : null
        );
    }

    /**
     * @param array $memberships
     *
     * @return Membership[]
     */
    public function denormalizeMemberships(array $memberships)
    {
        $result = [];
        foreach ($memberships as $membership) {
            $result[] = $this->denormalizeMembership($membership);
        }

        return $result;
    }

    /**
     * @param array $membership
     *
     * @return Membership
     */
    public function denormalizeMembership(array $membership)
    {
        return new Membership(
            $membership['role'],
            $this->denormalizeGroup($membership['group'])
        );
    }
}
