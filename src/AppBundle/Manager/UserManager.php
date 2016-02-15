<?php

namespace AppBundle\Manager;

use AppBundle\Api\ApiClient;
use AppBundle\Model\User;

/**
 * Class UserManager
 */
class UserManager
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
}
