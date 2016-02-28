<?php

namespace AppBundle\Security;

use AppBundle\Manager\GroupManager;
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
            if (array_key_exists(1, $otherGroups)) {
                return true;
            }
        }

        return false;
    }
}
