<?php

namespace AppBundle\Manager;

use AppBundle\Api\ApiClient;
use AppBundle\Model\Notification;

/**
 * Class NotificationManager
 */
class NotificationManager
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
     * @param int $id
     *
     * @return Notification[]
     */
    public function findNotifications($id)
    {
        return $this->client->findNotifications($id);
    }

    /**
     * @param int $userId
     * @param int $groupId
     *
     * @return Notification[]
     */
    public function findNotificationsForGroup($userId, $groupId)
    {
        /** @var Notification[] $notifications */
        $notifications = $this->client->findNotifications($userId, $groupId);

        $result = [];
        foreach ($notifications as $notification) {
            $result[$notification->getFrom()->getId()] = $notification;
        }

        return $result;
    }

    /**
     * @param int $userId
     * @param int $id
     */
    public function processNotification($userId, $id)
    {
        $this->client->removeNotification($userId, $id);
    }
}
