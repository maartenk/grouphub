<?php

namespace AppBundle\Ldap;

use AppBundle\Model\Group;
use AppBundle\Model\User;

/**
 * Class Normalizer
 */
class Normalizer
{
    /**
     * @param array $users
     *
     * @return User[]
     */
    public function denormalizeUsers(array $users)
    {
        $result = [];
        for ($i = 0; $i < $users['count']; $i++) {
            $user = $users[$i];

            $result[] = new User(
                null, $user['dn'], $user['givenname'][0], $user['sn'][0], $user['uid'][0]
            );
        }

        return $result;
    }

    /**
     * @param array $groups
     *
     * @return Group[]
     */
    public function denormalizeGroups(array $groups)
    {
        $result = [];
        for ($i = 0; $i < $groups['count']; $i++) {
            $group = $groups[$i];

            $result[] = new Group(
                null, $group['dn'], $group['cn'][0], '', 'ldap', 1
            );
        }

        return $result;
    }

    /**
     * @param array $groups
     *
     * @return User[]
     */
    public function denormalizeGroupUsers(array $groups)
    {
        $result = [];
        for ($i = 0; $i < $groups['count']; $i++) {
            $group = $groups[$i]['member'];

            for ($j = 0; $j < $group['count']; $j ++) {
                if (empty($group[$j])) {
                    continue;
                }

                $result[$group[$j]] = new User(
                    null, $group[$j]
                );
            }
        }

        // Manually sort the results, because ldap is unable to do this
        ksort($result);

        return array_values($result);
    }

    /**
     * @param array $groups
     *
     * @return User[]
     */
    public function denormalizeGrouphubGroups(array $groups)
    {
        $result = [];
        for ($i = 0; $i < $groups['count']; $i++) {
            $group = $groups[$i];

            $result[] = new Group(
                null, $group['dn'], $group['cn'][0], ''
            );
        }

        return $result;
    }

    /**
     * @param Group $group
     *
     * @return array
     */
    public function normalizeGroup(Group $group)
    {
        return [
            'cn'          => $group->getName(),
            'objectClass' => 'groupOfNames',  // @todo: inject??
            'member'      => '',
        ];
    }
}
