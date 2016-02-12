<?php

namespace AppBundle\Security;

use AppBundle\Manager\GroupManager;
use AppBundle\Model\Group;
use AppBundle\Model\Membership;
use AppBundle\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class GroupVoter
 */
class GroupVoter extends Voter
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
        return $attribute === 'EDIT';
    }

    /**
     * @inheritdoc
     *
     * @param Group $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($subject->getOwnerId() == $user->getId()) {
            return true;
        }

        $membership = $this->groupManager->findUserMembershipOfGroup($user->getId(), $subject->getId());

        if (!$membership) {
            return false;
        }

        if ($membership->getRole() === Membership::ROLE_ADMIN) {
            return true;
        }

        return false;
    }
}
