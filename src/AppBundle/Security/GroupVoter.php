<?php

namespace AppBundle\Security;

use AppBundle\Manager\MembershipManager;
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
     * @var MembershipManager
     */
    private $membershipManager;

    /**
     * @param MembershipManager $membershipManager
     */
    public function __construct(MembershipManager $membershipManager)
    {
        $this->membershipManager = $membershipManager;
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

        $membership = $this->membershipManager->findUserMembershipOfGroup($subject->getId(), $user->getId());

        if (!$membership) {
            return false;
        }

        if ($membership->getRole() === Membership::ROLE_ADMIN) {
            return true;
        }

        return false;
    }
}
