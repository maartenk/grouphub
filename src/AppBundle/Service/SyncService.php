<?php

namespace AppBundle\Service;

use AppBundle\Api\ApiClient;
use AppBundle\Ldap\GrouphubClient;
use AppBundle\Model\Group;
use AppBundle\Model\Membership;
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
     * @param GrouphubClient $ldap
     * @param ApiClient      $api
     * @param Logger         $logger
     */
    public function __construct(GrouphubClient $ldap, ApiClient $api, Logger $logger)
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
     * @param int $offset
     */
    public function syncUsers($offset = 0)
    {
        $this->logger->info('Processing users ' . $offset . ' to ' . self::BATCH_SIZE . '...');

        $ldapUsers = $this->ldap->findUsers($offset, self::BATCH_SIZE);
        $grouphubUsers = $this->api->findUsers(null, $offset, self::BATCH_SIZE);

        if (count($ldapUsers) === 0 && count($grouphubUsers) === 0) {
            $this->logger->info('Done syncing users!');
            return;
        }

        $index = $grouphubUsers->synchronize($ldapUsers, true);

        $this->logger->info(' - Going to add ' . count($grouphubUsers->getAddedElements()) . ' users to Grouphub...');
        foreach ($grouphubUsers->getAddedElements() as $element) {
            /** @var User $element */
            $this->api->addUser($element);
        }

        $this->logger->info(' - Going to update ' . count($grouphubUsers->getUpdatedElements()) . ' users in Grouphub...');
        foreach ($grouphubUsers->getUpdatedElements() as $element) {
            /** @var User[] $element */
            $this->api->updateUser($element['old']->getId(), $element['new']);
        }

        $this->logger->info(' - Going to remove ' . count($grouphubUsers->getRemovedElements()) . ' users from Grouphub...');
        foreach ($grouphubUsers->getRemovedElements() as $element) {
            /** @var User $element */
            $this->api->removeUser($element->getId());
        }

        $this->syncUsers($offset + $index + 1);
    }

    /**
     * @param int $offset
     */
    public function syncGroups($offset = 0)
    {
        $this->logger->info('Processing groups ' . $offset . ' to ' . self::BATCH_SIZE . '...');

        $ldapGroups = $this->ldap->findGroups($offset, self::BATCH_SIZE);
        $grouphubGroups = $this->api->findLdapGroups($offset, self::BATCH_SIZE);

        if (count($ldapGroups) === 0 && count($grouphubGroups) === 0) {
            $this->logger->info('Done syncing groups!');
            return;
        }

        $index = $grouphubGroups->synchronize($ldapGroups, true);

        $this->logger->info(' - Going to add ' . count($grouphubGroups->getAddedElements()) . ' groups to Grouphub...');
        foreach ($grouphubGroups->getAddedElements() as $element) {
            /** @var Group $element */
            $element = $this->api->addGroup($element);

            $this->syncGroupUsers($element);
        }

        $this->logger->info(' - Going to update ' . count($grouphubGroups->getUpdatedElements()) . ' groups in Grouphub...');
        foreach ($grouphubGroups->getUpdatedElements() as $element) {
            /** @var Group[] $element */
            $this->api->updateGroup($element['old']->getId(), $element['new']);

            $this->syncGroupUsers($element['old']);
        }

        $this->logger->info(' - Going to remove ' . count($grouphubGroups->getRemovedElements()) . ' groups from Grouphub...');
        foreach ($grouphubGroups->getRemovedElements() as $element) {
            /** @var Group $element */
            $this->api->removeGroup($element->getId());
        }

        foreach ($grouphubGroups->getEqualElementIndexes() as $index) {
            /** @var Group $element */
            $this->syncGroupUsers($grouphubGroups[$index]);
        }

        $this->syncGroups($offset + $index + 1);
    }

    /**
     * @param Group $group
     * @param int   $offset
     */
    private function syncGroupUsers(Group $group, $offset = 0)
    {
        $this->logger->info(
            'Processing users for Group `' . $group->getName() . '` ' . $offset . ' to ' . self::BATCH_SIZE . '...'
        );

        $ldapUsers = $this->ldap->findGroupUsers($group->getReference(), $offset, self::BATCH_SIZE);
        $grouphubUsers = $this->api->findGroupUsers($group, $offset, self::BATCH_SIZE);

        // Nothing to sync, or done syncing
        if (count($ldapUsers) === 0 && count($grouphubUsers) === 0) {
            $this->logger->info('Done syncing Group users!');
            return;
        }

        $index = $grouphubUsers->synchronize($ldapUsers, true);

        $this->logger->info(' - Going to add ' . count($grouphubUsers->getAddedElements()) . ' users for Group to Grouphub...');
        foreach ($grouphubUsers->getAddedElements() as $element) {
            /** @var User $element */
            $element = $this->api->findUserByReference($element->getReference());
            $this->api->addGroupUser($group->getId(), $element->getId());
        }

        $this->logger->info(' - Going to remove ' . count($grouphubUsers->getRemovedElements()) . ' users for Group from Grouphub...');
        foreach ($grouphubUsers->getRemovedElements() as $element) {
            /** @var User $element */
            $this->api->removeGroupUser($group->getId(), $element->getId());
        }

        $this->syncGroupUsers($group, $offset + $index + 1);
    }

    /**
     * @param int $offset
     */
    public function syncGrouphubGroups($offset = 0)
    {
        $this->logger->info('Processing Grouphub groups ' . $offset . ' to ' . self::BATCH_SIZE . '...');

        $grouphubGroups = $this->api->findGrouphubGroups($offset, self::BATCH_SIZE);
        $ldapGroups = $this->ldap->findGrouphubGroups($offset, self::BATCH_SIZE);

        if (count($grouphubGroups) === 0 && count($ldapGroups) === 0) {
            $this->logger->info('Done syncing Grouphub groups!');
            return;
        }

        $index = $ldapGroups->synchronize($grouphubGroups, true);

        $this->logger->info(' - Going to add ' . count($ldapGroups->getAddedElements()) . ' Grouphub groups to LDAP...');
        foreach ($ldapGroups->getAddedElements() as $element) {
            /** @var Group $element */
            $this->ldap->addGroup($element);

            // Update the reference of the Group in the API
            $this->api->updateGroupReference($element->getId(), $element->getReference());

            $this->syncGrouphubGroupUsers($element);
            $this->syncGrouphubGroupAdmins($element);
        }

        $this->logger->info(
            ' - Going to update ' . count($ldapGroups->getUpdatedElements()) . ' Grouphub groups in LDAP... (NOT SUPPORTED, SKIPPING)'
        );
        foreach ($ldapGroups->getUpdatedElements() as $element) {
            /** @var Group[] $element */
            $this->ldap->updateGroup($element['old']->getReference(), $element['new']);

            $this->syncGrouphubGroupUsers($element['new']);
            $this->syncGrouphubGroupAdmins($element['new']);
        }

        $this->logger->info(' - Going to remove ' . count($ldapGroups->getRemovedElements()) . ' Grouphub groups from LDAP...');
        foreach ($ldapGroups->getRemovedElements() as $element) {
            /** @var Group $element */
            $this->ldap->removeGroup($element);
        }

        foreach ($ldapGroups->getEqualElementIndexes() as $index) {
            /** @var Group $element */
            $this->syncGrouphubGroupUsers($grouphubGroups[$index]);
            $this->syncGrouphubGroupAdmins($grouphubGroups[$index]);
        }

        $this->syncGrouphubGroups($offset + $index + 1);
    }

    /**
     * @param Group $group
     * @param int   $offset
     */
    private function syncGrouphubGroupUsers(Group $group, $offset = 0)
    {
        $this->logger->info(
            'Processing users for GrouphubGroup `' . $group->getName() . '` ' . $offset . ' to ' . self::BATCH_SIZE . '...'
        );

        $grouphubUsers = $this->api->findGroupUsers($group, $offset, self::BATCH_SIZE);
        $ldapUsers = $this->ldap->findGroupUsers($group->getReference(), $offset, self::BATCH_SIZE);

        if (count($grouphubUsers) === 0 && count($ldapUsers) === 0) {
            $this->logger->info('Done syncing GroupHubGroup users!');
            return;
        }

        $index = $ldapUsers->synchronize($grouphubUsers, true);

        $this->logger->info(' - Going to add ' . count($ldapUsers->getAddedElements()) . ' users for GrouphubGroup to LDAP...');
        foreach ($ldapUsers->getAddedElements() as $element) {
            /** @var User $element */
            $this->ldap->addGroupUser($group->getReference(), $element->getReference());
        }

        $this->logger->info(' - Going to remove ' . count($ldapUsers->getRemovedElements()) . ' users for GrouphubGroup from LDAP...');
        foreach ($ldapUsers->getRemovedElements() as $element) {
            /** @var User $element */
            $this->ldap->removeGroupUser($group->getReference(), $element->getReference());
        }

        $this->syncGrouphubGroupUsers($group, $offset + $index + 1);
    }

    /**
     * @param Group $group
     * @param int   $offset
     */
    private function syncGrouphubGroupAdmins(Group $group, $offset = 0)
    {
        $this->logger->info(
            'Processing admins for GrouphubGroup `' . $group->getName() . '` ' . $offset . ' to ' . self::BATCH_SIZE . '...'
        );

        $grouphubAdmins = $this->api->findGroupUsers($group, $offset, self::BATCH_SIZE, Membership::ROLE_ADMIN);
        $ldapAdmins = $this->ldap->findGroupAdmins($group, $offset, self::BATCH_SIZE);

        if (count($grouphubAdmins) === 0 && count($ldapAdmins) === 0) {
            $this->logger->info('Done syncing GroupHubGroup admins!');
            return;
        }

        $index = $ldapAdmins->synchronize($grouphubAdmins, true);

        $this->logger->info(' - Going to add ' . count($ldapAdmins->getAddedElements()) . ' admins for GrouphubGroup to LDAP...');
        foreach ($ldapAdmins->getAddedElements() as $element) {
            /** @var User $element */
            $this->ldap->addGroupAdmin($group, $element->getReference());
        }

        $this->logger->info(' - Going to remove ' . count($ldapAdmins->getRemovedElements()) . ' admins for GrouphubGroup from LDAP...');
        foreach ($ldapAdmins->getRemovedElements() as $element) {
            /** @var User $element */
            $this->ldap->removeGroupAdmin($group, $element->getReference());
        }

        $this->syncGrouphubGroupAdmins($group, $offset + $index + 1);
    }
}
