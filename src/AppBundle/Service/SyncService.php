<?php

namespace AppBundle\Service;

use AppBundle\Api\ApiClient;
use AppBundle\Ldap\LdapClient;
use Monolog\Logger;

/**
 * Class SyncService
 *
 * @todo: batch API operations
 */
class SyncService
{
    const BATCH_SIZE = 10;

    /**
     * @param LdapClient $ldap
     * @param ApiClient  $api
     * @param Logger     $logger
     */
    public function __construct(LdapClient $ldap, ApiClient $api, Logger $logger)
    {
        $this->ldap = $ldap;
        $this->api = $api;
        $this->logger = $logger;
    }

    /**
     *
     */
    public function sync()
    {
        $this->syncUsers();
        $this->syncGroups();
        // $this->syncGrouphubGroups();
    }

    /**
     * Walk through the source sequence and synchronise the destination set
     *
     * @param int $offset
     */
    private function syncUsers($offset = 0)
    {
        $this->logger->info('Processing users ' . $offset . ' to ' . self::BATCH_SIZE . '...');

        $ldapUsers = $this->ldap->findUsers($offset, self::BATCH_SIZE);
        $grouphubUsers = $this->api->findUsers($offset, self::BATCH_SIZE);

        // Nothing to sync, or done syncing
        if (count($ldapUsers) === 0 && count($grouphubUsers) === 0) {
            $this->logger->info('Done syncing users!');
            return;
        }

        $index = $grouphubUsers->synchronize($ldapUsers, true);

        $this->logger->info('Going to add ' . count($grouphubUsers->getAddedElements()) . ' users to Grouphub...');
        foreach ($grouphubUsers->getAddedElements() as $element) {
            $this->api->addUser($element);
        }

        $this->logger->info('Going to update ' . count($grouphubUsers->getUpdatedElements()) . ' users in Grouphub...');
        foreach ($grouphubUsers->getUpdatedElements() as $element) {
            $this->api->updateUser($element['old']->getId(), $element['new']);
        }

        $this->logger->info('Going to remove ' . count($grouphubUsers->getRemovedElements()) . ' users from Grouphub...');
        foreach ($grouphubUsers->getRemovedElements() as $element) {
            $this->api->removeUser($element->getId());
        }

        $this->syncUsers($offset + $index + 1);
    }

    /**
     * @param int $offset
     */
    private function syncGroups($offset = 0)
    {
        $this->logger->info('Processing groups ' . $offset . ' to ' . self::BATCH_SIZE . '...');

        $ldapGroups = $this->ldap->findGroups($offset, self::BATCH_SIZE);
        $grouphubGroups = $this->api->findLdapGroups($offset, self::BATCH_SIZE);

        // Nothing to sync, or done syncing
        if (count($ldapGroups) === 0 && count($grouphubGroups) === 0) {
            $this->logger->info('Done syncing groups!');
            return;
        }

        $index = $grouphubGroups->synchronize($ldapGroups, true);

        $this->logger->info('Going to add ' . count($grouphubGroups->getAddedElements()) . ' groups to Grouphub...');
        foreach ($grouphubGroups->getAddedElements() as $element) {
            $this->api->addGroup($element);

            // @todo: add members??
        }

        $this->logger->info('Going to update ' . count($grouphubGroups->getUpdatedElements()) . ' groups in Grouphub...');
        foreach ($grouphubGroups->getUpdatedElements() as $element) {
            $this->api->updateGroup($element['old']->getId(), $element['new']);

            // @todo: update members?
        }

        $this->logger->info('Going to remove ' . count($grouphubGroups->getRemovedElements()) . ' groups from Grouphub...');
        foreach ($grouphubGroups->getRemovedElements() as $element) {
            $this->api->removeGroup($element->getId());

            // @todo: delete members?
        }

        $this->syncGroups($offset + $index + 1);
    }

    /**
     * @param int $offset
     *
     * @todo: implement, test
     */
    private function syncGrouphubGroups($offset = 0)
    {
        $grouphubGroups = $this->api->findGrouphubGroups($offset, self::BATCH_SIZE);

        // Nothing to sync, or done syncing
        if (count($grouphubGroups) === 0) {
            return;
        }

        $ldapGroups = $this->ldap->findGrouphubGroups($offset, self::BATCH_SIZE);

        $index = $ldapGroups->synchronize($grouphubGroups, true);

        foreach ($ldapGroups->getAddedElements() as $element) {
            $this->ldap->addGroup($element);

            // @todo: add members??
        }

        foreach ($ldapGroups->getUpdatedElements() as $element) {
            $this->ldap->updateGroup($element);

            // @todo: update members?
        }

        foreach ($ldapGroups->getAddedElements() as $element) {
            $this->ldap->removeGroup($element->getId());

            // @todo: delete members?
        }

        $this->syncGrouphubGroups($index);
    }
}
