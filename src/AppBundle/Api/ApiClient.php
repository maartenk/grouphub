<?php

namespace AppBundle\Api;

use AppBundle\Model\Group;
use AppBundle\Model\User;
use AppBundle\SynchronizableSequence;
use GuzzleHttp\Client;
use RuntimeException;

/**
 * Class ApiClient
 *
 * @todo: catch (http) exceptions
 * @todo: use actual normalizer/serializer
 */
class ApiClient
{
    /**
     * @var Client
     */
    private $guzzle;

    /**
     * @param Client $guzzle
     */
    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return SynchronizableSequence
     */
    public function findUsers($offset = 0, $limit = 100)
    {
        $data = $this->guzzle->get(
            'users',
            ['query' => ['offset' => $offset, 'limit' => $limit, 'sort' => 'reference']]
        );

        $data = $this->decode($data->getBody());

        return new SynchronizableSequence($this->denormalizeUsers($data));
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return SynchronizableSequence
     */
    public function findLdapGroups($offset = 0, $limit = 100)
    {
        $data = $this->guzzle->get(
            'groups',
            ['query' => ['offset' => $offset, 'limit' => $limit, 'sort' => 'reference', 'type' => 'ldap']]
        );

        $data = $this->decode($data->getBody());

        return new SynchronizableSequence($this->denormalizeGroups($data));
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return SynchronizableSequence
     */
    public function findGrouphubGroups($offset = 0, $limit = 100)
    {
        $data = $this->guzzle->get(
            'groups',
            ['query' => ['offset' => $offset, 'limit' => $limit, 'sort' => 'reference', 'type' => 'grouphub']]
        );

        $data = $this->decode($data->getBody());

        return new SynchronizableSequence($this->denormalizeGroups($data));
    }

    /**
     * @param User $user
     */
    public function addUser(User $user)
    {
        $data = $this->encode(['user' => $this->normalizeUser($user)]);

        $this->guzzle->post('users', ['body' => $data]);
    }

    /**
     * @param Group $group
     */
    public function addGroup(Group $group)
    {
        $data = $this->encode(['group' => $this->normalizeGroup($group)]);

        $this->guzzle->post('groups', ['body' => $data]);
    }

    /**
     * @param int  $userId
     * @param User $user
     */
    public function updateUser($userId, User $user)
    {
        $data = $this->encode(['user' => $this->normalizeUser($user)]);

        $this->guzzle->put('users/' . $userId, ['body' => $data]);
    }

    /**
     * @param int   $groupId
     * @param Group $group
     */
    public function updateGroup($groupId, Group $group)
    {
        $data = $this->encode(['group' => $this->normalizeGroup($group)]);

        $this->guzzle->put('groups/' . $groupId, ['body' => $data]);
    }

    /**
     * @param int $userId
     */
    public function removeUser($userId)
    {
        $this->guzzle->delete('users/' . $userId);
    }

    /**
     * @param int $groupId
     */
    public function removeGroup($groupId)
    {
        $this->guzzle->delete('groups/' . $groupId);
    }

    /**
     * @param string $data
     *
     * @return mixed
     */
    private function decode($data)
    {
        $data = json_decode($data, true);

        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            throw new RuntimeException(sprintf('Error decoding JSON, error no %i', $error));
        }

        return $data;
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    private function encode($data)
    {
        $data = json_encode($data);

        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            throw new RuntimeException(sprintf('Error encoding JSON, error no %i', $error));
        }

        return $data;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    private function normalizeUser(User $user)
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
    private function denormalizeUsers(array $users)
    {
        $result = [];
        foreach ($users as $user) {
            $result[] = new User(
                $user['id'],
                $user['reference'],
                isset($user['first_name']) ? $user['first_name'] : '',
                isset($user['last_name']) ? $user['last_name'] : '',
                isset($user['login_name']) ? $user['login_name'] : ''
            );
        }

        return $result;
    }

    /**
     * @param Group $group
     *
     * @return array
     */
    private function normalizeGroup(Group $group)
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
    private function denormalizeGroups(array $groups)
    {
        $result = [];
        foreach ($groups as $group) {
            $result[] = new Group(
                $group['id'],
                $group['reference'],
                isset($group['name']) ? $group['name'] : '',
                isset($group['description']) ? $group['description'] : '',
                isset($group['type']) ? $group['type'] : '',
                isset($group['owner']['id']) ? $group['owner']['id'] : null,
                isset($group['parent']['id']) ? $group['parent']['id'] : null
            );
        }

        return $result;
    }
}
