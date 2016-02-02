<?php

namespace AppBundle\Controller;

use AppBundle\Model\Membership;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class IndexController
 */
class IndexController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction()
    {
        $user = $this->getUser();

        $apiClient = $this->get('app.api_client');

        /** @var Membership[] $myGroups */
        $myGroups = $apiClient->findUserMemberships(7);//$user->getId());

        $ownerGrouphubGroups = $adminGrouphubGroups = $memberGrouphubGroups = [];
        $ownerOtherGroups = $adminOtherGroups = $memberOtherGroups = [];
        foreach ($myGroups as $group) {
            if ($group->getGroup()->getType() === 'grouphub' && $group->getRole() == 'owner') {
                $ownerGrouphubGroups[$group->getGroup()->getId()] = $group->getGroup();
            }

            if ($group->getGroup()->getType() === 'grouphub' && $group->getRole() == 'admin') {
                $adminGrouphubGroups[$group->getGroup()->getId()] = $group->getGroup();
            }

            if ($group->getGroup()->getType() === 'grouphub' && $group->getRole() == 'member') {
                $memberGrouphubGroups[$group->getGroup()->getId()] = $group->getGroup();
            }

            if ($group->getGroup()->getType() !== 'grouphub' && $group->getRole() == 'owner') {
                $ownerOtherGroups[$group->getGroup()->getId()] = $group->getGroup();
            }

            if ($group->getGroup()->getType() !== 'grouphub' && $group->getRole() == 'admin') {
                $adminOtherGroups[$group->getGroup()->getId()] = $group->getGroup();
            }

            if ($group->getGroup()->getType() !== 'grouphub' && $group->getRole() == 'member') {
                $memberOtherGroups[$group->getGroup()->getId()] = $group->getGroup();
            }
        }

        return $this->render(':index:index.html.twig', [
            'ownerGrouphubGroups'  => $ownerGrouphubGroups,
            'adminGrouphubGroups'  => $adminGrouphubGroups,
            'memberGrouphubGroups' => $memberGrouphubGroups,
            'ownerOtherGroups'     => $ownerOtherGroups,
            'adminOtherGroups'     => $adminOtherGroups,
            'memberOtherGroups'    => $memberOtherGroups,
            'groups'               => $apiClient->findGroups(),
        ]);
    }
}
