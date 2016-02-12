<?php

namespace AppBundle\Controller;

use AppBundle\Form\GroupType;
use AppBundle\Model\Membership;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class IndexController
 *
 * @todo: refactor, also reorder templates
 */
class IndexController extends Controller
{
    /**
     * @Route("/{_locale}", defaults={"_locale": "en"}, requirements={"_locale": "en|nl"}, name="home")
     * @Method("GET")
     */
    public function indexAction()
    {
        $groups = $this->get('app.group_manager')->getMyGroups($this->getUser());
        $addForm = $this->createForm(
            GroupType::class,
            null,
            [
                'action' => $this->generateUrl('add_group'),
            ]
        );

        return $this->render(
            '::base.html.twig',
            [
                'ownerGrouphubGroups'  => isset($groups['grouphub']['owner']) ? $groups['grouphub']['owner'] : [],
                'adminGrouphubGroups'  => isset($groups['grouphub']['admin']) ? $groups['grouphub']['admin'] : [],
                'memberGrouphubGroups' => isset($groups['grouphub']['member']) ? $groups['grouphub']['member'] : [],
                'ownerOtherGroups'     => isset($groups['other']['owner']) ? $groups['other']['owner'] : [],
                'adminOtherGroups'     => isset($groups['other']['admin']) ? $groups['other']['admin'] : [],
                'memberOtherGroups'    => isset($groups['other']['member']) ? $groups['other']['member'] : [],
                'groups'               => $this->get('app.group_manager')->findGroups(),
                'add_form'             => $addForm->createView(),
            ]
        );
    }

    /**
     * @Route("/{_locale}/search", defaults={"_locale": "en"}, requirements={"_locale": "en|nl"}, name="search")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchAction(Request $request)
    {
        $query = $request->request->get('query');

        $groups = $this->get('app.group_manager')->getMyGroups($this->getUser());

        return $this->render(
            ':groups:search-results.html.twig',
            [
                'ownerGrouphubGroups'  => isset($groups['grouphub']['owner']) ? $groups['grouphub']['owner'] : [],
                'adminGrouphubGroups'  => isset($groups['grouphub']['admin']) ? $groups['grouphub']['admin'] : [],
                'memberGrouphubGroups' => isset($groups['grouphub']['member']) ? $groups['grouphub']['member'] : [],
                'ownerOtherGroups'     => isset($groups['other']['owner']) ? $groups['other']['owner'] : [],
                'adminOtherGroups'     => isset($groups['other']['admin']) ? $groups['other']['admin'] : [],
                'memberOtherGroups'    => isset($groups['other']['member']) ? $groups['other']['member'] : [],
                'groups'               => $this->get('app.group_manager')->findGroups($query),
                'query'                => $query,
            ]
        );
    }

    /**
     * @Route("/{_locale}/add_group", name="add_group")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return Response
     * @todo: convert to AJAX action?
     */
    public function addGroupAction(Request $request)
    {
        $form = $this->createForm(GroupType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $group = $this->get('app.api_client')->addGroup($form->getData());
            $this->get('app.grouphub_ldap_client')->addGroup($group);

            return $this->redirect($this->generateUrl('home'));
        }

        return new Response($form->getErrors(true));
    }

    /**
     * @Route("/{_locale}/group/{id}", name="group_details")
     * @Method("GET")
     *
     * @param int $id
     *
     * @return Response
     */
    public function groupDetailsAction($id)
    {
        $membership = $this->get('app.api_client')->findUserMembershipOfGroup($this->getUser()->getId(), $id);

        if ($membership) {
            $group = $membership->getGroup();
            $role = $membership->getRole();
        } else {
            $group = $this->get('app.group_manager')->getGroup($id);
            $role = null;
        }

        $members = $this->get('app.api_client')->findGroupMemberships($group->getId());

        return $this->render(
            ':popups:group_details.html.twig',
            [
                'group'   => $group,
                'members' => $members,
                'role'    => $role,
            ]
        );
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

        $this->get('app.group_manager')->updateMembership($groupId, $userId, $role);

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

        $this->get('app.group_manager')->deleteMembership($groupId, $userId);

        return new Response();
    }
}
