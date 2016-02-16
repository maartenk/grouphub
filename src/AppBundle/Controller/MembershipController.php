<?php

namespace AppBundle\Controller;

use AppBundle\Model\Membership;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class MembershipController
 */
class MembershipController extends Controller
{
    /**
     * @Route("/group/{groupId}/user/{userId}/add", name="membership_add")
     * @Method("POST")
     *
     * @param int $groupId
     * @param int $userId
     *
     * @return Response
     */
    public function addMembershipAction($groupId, $userId)
    {
        $group = $this->get('app.group_manager')->getGroup($groupId);

        if (empty($group)) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('EDIT', $group);

        $this->get('app.membership_manager')->addMembership($groupId, $userId);

        return new Response();
    }

    /**
     * @Route("/group/{groupId}/user/{userId}/update", name="membership_update")
     * @Method("POST")
     *
     * @param int     $groupId
     * @param int     $userId
     * @param Request $request
     *
     * @return Response
     */
    public function updateMembershipAction($groupId, $userId, Request $request)
    {
        $group = $this->get('app.group_manager')->getGroup($groupId);

        if (empty($group)) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('EDIT', $group);

        $role = $request->request->get('role');

        if (!in_array($role, [Membership::ROLE_ADMIN, Membership::ROLE_MEMBER])) {
            throw new BadRequestHttpException('Invalid role');
        }

        $this->get('app.membership_manager')->updateMembership($groupId, $userId, $role);

        return new Response();
    }

    /**
     * @Route("/group/{groupId}/user/{userId}/delete", name="membership_delete")
     * @Method("POST")
     *
     * @param int $groupId
     * @param int $userId
     *
     * @return Response
     */
    public function deleteMembershipAction($groupId, $userId)
    {
        $group = $this->get('app.group_manager')->getGroup($groupId);

        if (empty($group)) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('EDIT', $group);

        $this->get('app.membership_manager')->deleteMembership($groupId, $userId);

        return new Response();
    }

    /**
     * @Route("/group/{groupId}/me/delete", name="my_membership_delete")
     * @Method("POST")
     *
     * @param int $groupId
     *
     * @return Response
     */
    public function deleteMyMembership($groupId)
    {
        $group = $this->get('app.group_manager')->getGroup($groupId);

        if (empty($group)) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('EDIT_MEMBERSHIP', $group);

        $this->get('app.membership_manager')->deleteMembership($groupId, $this->getUser()->getId());

        return new Response();
    }
}
