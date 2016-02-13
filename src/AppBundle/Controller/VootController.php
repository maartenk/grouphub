<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VootController
 *
 * @Route("/voot")
 */
class VootController extends Controller
{
    /**
     * @Route("/user/{loginName}/groups", name="voot_groups")
     *
     * @param string $loginName
     *
     * @return Response
     */
    public function groupsAction($loginName)
    {
        $groupManager = $this->get('app.group_manager');

        $user = $groupManager->getUserByLoginName($loginName);

        if (empty($user)) {
            throw $this->createNotFoundException('User not found');
        }

        $memberships = $groupManager->findUserMemberships($user->getId());

        $result = [];

        foreach ($memberships as $membership) {
            $result[] = [
                'id'          => $membership->getGroup()->getId(),
                'displayName' => $membership->getGroup()->getName(),
                'description' => $membership->getGroup()->getDescription(),
                'sourceID'    => $membership->getGroup()->getReference(),
                'membership'  => [
                    'basic' => $membership->getRole(),
                ],
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/user/{loginName}/groups/{groupId}", name="voot_group")
     *
     * @param string $loginName
     * @param int    $groupId
     *
     * @return Response
     */
    public function groupAction($loginName, $groupId)
    {
        $groupManager = $this->get('app.group_manager');

        $user = $groupManager->getUserByLoginName($loginName);

        if (empty($user)) {
            throw $this->createNotFoundException('User not found');
        }

        $membership = $groupManager->findUserMembershipOfGroup($groupId, $user->getId());

        if ($membership === null) {
            throw $this->createNotFoundException();
        }

        $result = [
            'basic' => $membership->getRole(),
        ];

        return new JsonResponse($result);
    }
}
