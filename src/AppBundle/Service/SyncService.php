<?php

namespace AppBundle\Service;

use AppBundle\Api\ApiClient;
use AppBundle\Ldap\LdapClient;
use AppBundle\Model\Group;
use AppBundle\Model\User;
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
        $this->syncGrouphubGroups();
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
            /** @var User $element */
            $this->api->addUser($element);
        }

        $this->logger->info('Going to update ' . count($grouphubUsers->getUpdatedElements()) . ' users in Grouphub...');
        foreach ($grouphubUsers->getUpdatedElements() as $element) {
            /** @var User[] $element */
            $this->api->updateUser($element['old']->getId(), $element['new']);
        }

        $this->logger->info('Going to remove ' . count($grouphubUsers->getRemovedElements()) . ' users from Grouphub...');
        foreach ($grouphubUsers->getRemovedElements() as $element) {
            /** @var User $element */
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
            /** @var Group $element */
            $this->api->addGroup($element);

            // @todo: ID is not known yet...
            $this->syncGroupUsers($element->getId());
        }

        $this->logger->info('Going to update ' . count($grouphubGroups->getUpdatedElements()) . ' groups in Grouphub...');
        foreach ($grouphubGroups->getUpdatedElements() as $element) {
            /** @var Group[] $element */
            $this->api->updateGroup($element['old']->getId(), $element['new']);

            $this->syncGroupUsers($element['old']->getId());
        }

        $this->logger->info('Going to remove ' . count($grouphubGroups->getRemovedElements()) . ' groups from Grouphub...');
        foreach ($grouphubGroups->getRemovedElements() as $element) {
            /** @var Group $element */
            $this->api->removeGroup($element->getId());
        }

        foreach ($grouphubGroups->getEqualElements() as $element) {
            /** @var Group $element */
            $this->syncGroupUsers($element->getId());
        }

        $this->syncGroups($offset + $index + 1);
    }

    /**
     * @param int $groupId
     * @param int $offset
     */
    private function syncGroupUsers($groupId, $offset = 0)
    {
        // @todo: implement, test
    }

    /**
     * @param int $offset
     */
    private function syncGrouphubGroups($offset = 0)
    {
        $this->logger->info('Processing Grouphub groups ' . $offset . ' to ' . self::BATCH_SIZE . '...');

        $grouphubGroups = $this->api->findGrouphubGroups($offset, self::BATCH_SIZE);
        $ldapGroups = $this->ldap->findGrouphubGroups($offset, self::BATCH_SIZE);

        // Nothing to sync, or done syncing
        if (count($grouphubGroups) === 0 && count($ldapGroups) === 0) {
            $this->logger->info('Done syncing Grouphub groups!');
            return;
        }

        $index = $ldapGroups->synchronize($grouphubGroups, true);

        $this->logger->info('Going to add ' . count($ldapGroups->getAddedElements()) . ' Grouphub groups to LDAP...');
        foreach ($ldapGroups->getAddedElements() as $element) {
            /** @var Group $element */
            $this->ldap->addGroup($element);

            // Update the reference of the Group in the API
            $this->api->updateGroupReference($element->getId(), $element->getReference());

            $this->syncGrouphubGroupUsers($element->getReference());
        }

        $this->logger->info('Going to update ' . count($ldapGroups->getUpdatedElements()) . ' Grouphub groups in LDAP... (NOT SUPPORTED, SKIPPING)');
        foreach ($ldapGroups->getUpdatedElements() as $element) {
            /** @var Group[] $element */
            $this->ldap->updateGroup($element['old']->getReference(), $element['new']);

            $this->syncGrouphubGroupUsers($element['old']->getReference());
        }

        $this->logger->info('Going to remove ' . count($ldapGroups->getRemovedElements()) . ' Grouphub groups from LDAP...');
        foreach ($ldapGroups->getRemovedElements() as $element) {
            /** @var Group $element */
            $this->ldap->removeGroup($element->getReference());
        }

        foreach ($ldapGroups->getEqualElements() as $element) {
            /** @var Group $element */
            $this->syncGrouphubGroupUsers($element->getReference());
        }

        $this->syncGrouphubGroups($offset + $index + 1);
    }

    /**
     * @param string $groupReference
     * @param int    $offset
     */
    private function syncGrouphubGroupUsers($groupReference, $offset = 0)
    {
        // @todo: implement, test
    }
}
