<?php

namespace AppBundle\Controller;

use AppBundle\Model\Membership;
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
     * @Route("/user/{userId}/groups", name="voot_groups")
     *
     * @param int $userId
     *
     * @return Response
     */
    public function groupsAction($userId)
    {
        /** @var Membership[] $memberships */
        $memberships = $this->get('app.api_client')->findUserMemberships($userId);

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
     * @Route("/user/{userId}/groups/{groupId}", name="voot_group")
     *
     * @param int $userId
     * @param int $groupId
     *
     * @return Response
     */
    public function groupAction($userId, $groupId)
    {
        $membership = $this->get('app.api_client')->findUserMembershipOfGroup($userId, $groupId);

        if ($membership === null) {
            throw $this->createNotFoundException();
        }

        $result = [
            'basic' => $membership->getRole(),
        ];

        return new JsonResponse($result);
    }
}
