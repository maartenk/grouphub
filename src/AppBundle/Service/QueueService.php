<?php

namespace AppBundle\Service;

use Doctrine\Common\Cache\Cache;

/**
 * Class QueueService
 */
class QueueService
{
    const QUEUE_CACHE_ID = 'grouphub-groups-queue';

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return int[]
     */
    public function getQueuedGroups()
    {
        if (!$this->cache->contains(self::QUEUE_CACHE_ID)) {
            return [];
        }

        $groupIds = $this->cache->fetch(self::QUEUE_CACHE_ID);

        if (!is_array($groupIds)) {
            return [];
        }

        return $groupIds;
    }

    /**
     *
     */
    public function clearGroupQueue()
    {
        $this->cache->delete(self::QUEUE_CACHE_ID);
    }

    /**
     * @param int $id
     */
    public function addGroupToQueue($id)
    {
        $groupIds = [];

        if ($this->cache->contains(self::QUEUE_CACHE_ID)) {
            $groupIds = $this->cache->fetch(self::QUEUE_CACHE_ID);
        }

        $groupIds[$id] = $id;

        $this->cache->save(self::QUEUE_CACHE_ID, $groupIds);
    }
}
