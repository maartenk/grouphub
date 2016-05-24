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
     * @var array
     */
    private $mapping;

    /**
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @param array $users
     *
     * @return User[]
     */
    public function denormalizeUsers(array $users)
    {
        $mapping = $this->mapping['user'];

        $result = [];
        for ($i = 0; $i < $users['count']; $i++) {
            $user = $users[$i];

            $annotations = [];

            if (isset($user[$mapping['email']][0])) {
                $annotations['email'] = $user[$mapping['email']][0];
            }

            $result[] = new User(
                null,
                $user['dn'],
                $user[$mapping['firstName']][0],
                $user[$mapping['lastName']][0],
                $user[$mapping['loginName']][0],
                $annotations
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
        $mapping = $this->mapping['group'];

        $result = [];
        for ($i = 0; $i < $groups['count']; $i++) {
            $group = $groups[$i];

            $result[] = new Group(
                null,
                $group['dn'],
                $group['cn'][0],
                isset($group[$mapping['description']][0]) ? $group[$mapping['description']][0] : '',
                'ldap',
                new User(1)
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

            for ($j = 0; $j < $group['count']; $j++) {
                if (empty($group[$j])) {
                    continue;
                }

                $result[$group[$j]] = new User(null, $group[$j]);
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
        $mapping = $this->mapping['group'];

        $result = [];
        for ($i = 0; $i < $groups['count']; $i++) {
            $group = $groups[$i];

            $result[] = new Group(
                null,
                $group['dn'],
                $group['cn'][0],
                isset($group[$mapping['description']][0]) ? $group[$mapping['description']][0] : ''
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
        $mapping = $this->mapping['group'];

        $cn = $this->getGroupCN($group);

        $data = array_filter(
            [
                'cn'                    => $cn,
                $mapping['description'] => $group->getDescription(),
                'objectClass'           => $mapping['objectClass'],
                'groupType'             => $mapping['groupType'],
            ]
        );

        $data['member'] = '';

        if (!empty($mapping['accountName'])) {
            $data[$mapping['accountName']] = $cn;
        }

        return $data;
    }

    /**
     * @param Group $group
     *
     * @return array
     */
    public function normalizeGroupForUpdate(Group $group)
    {
        $mapping = $this->mapping['group'];

        return array_filter(
            [
                $mapping['description'] => $group->getDescription(),
            ]
        );
    }

    /**
     * @param Group $group
     *
     * @return string
     */
    private function getGroupCN(Group $group)
    {
        $cn = str_replace(
            ['"', '/', '\\', '[', ']', ':', ';', '|', '=', ',', '+', '*', '?', '<', '>'],
            '',
            $group->getName()
        );

        $cn = strtolower($cn) . '_' . $group->getId();

        return $cn;
    }
}
