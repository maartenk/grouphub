<?php

namespace AppBundle\Security;

use AppBundle\Manager\GroupManager;
use AppBundle\Model\Group;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AdminVoter
 */
class AdminVoter extends Voter
{
    /**
     * @var GroupManager
     */
    private $groupManager;

    /**
     * @param GroupManager $groupManager
     */
    public function __construct(GroupManager $groupManager)
    {
        $this->groupManager = $groupManager;
    }

    /**
     * @inheritdoc
     */
    protected function supports($attribute, $subject)
    {
        return $attribute === 'ROLE_ADMIN';
    }

    /**
     * @inheritdoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        $groups = $this->groupManager->getMyGroups($user);

        if (empty($groups['other'])) {
            return false;
        }

        foreach ($groups['other'] as $otherGroups) {
            /** @var Group[] $otherGroups */

            // Admin if member of root admin group
            if (array_key_exists(1, $otherGroups)) {
                return true;
            }

            // Or if member of child of root admin group
            foreach ($otherGroups as $group) {
                if ($group->getParentId() === 1) {
                    return true;
                }
            }
        }

        return false;
    }
}
