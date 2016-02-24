<?php

namespace AppBundle\Controller;

use AppBundle\Form\GroupType;
use AppBundle\Model\Group;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GroupController
 */
class GroupController extends Controller
{
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
            $this->get('app.group_manager')->addGroup($form->getData());

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
        $group = $this->getGroup($id);

        $members = $this->get('app.membership_manager')->findGroupMemberships($group->getId());

        $users = $form = $notifications = null;
        if ($this->isGranted('EDIT', $group)) {
            $users = $this->get('app.user_manager')->findUsers();
            $form = $this->createForm(GroupType::class, $group)->createView();

            $notifications = $this->get('app.notification_manager')->findNotificationsForGroup(
                $this->getUser()->getId(),
                $group->getId()
            );
        }

        return $this->render(
            ':popups:group_details.html.twig',
            [
                'group'         => $group,
                'members'       => $members,
                'users'         => $users,
                'form'          => $form,
                'notifications' => $notifications,
            ]
        );
    }

    /**
     * @Route("/{_locale}/group/{id}/edit", name="edit_group")
     * @Method("POST")
     *
     * @param int     $id
     * @param Request $request
     *
     * @return Response
     */
    public function editGroupAction($id, Request $request)
    {
        $group = $this->getGroup($id);

        $this->denyAccessUnlessGranted('EDIT', $group);

        $form = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.group_manager')->updateGroup($group);

            return new Response();
        }

        return new Response($form->getErrors(true));
    }

    /**
     * @Route("/{_locale}/group/{id}/delete", name="delete_group")
     * @Method("POST")
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteGroupAction($id)
    {
        $group = $this->getGroup($id);

        $this->denyAccessUnlessGranted('EDIT', $group);

        $this->get('app.group_manager')->deleteGroup($group->getId());

        return new Response();
    }

    /**
     * @Route("/{_locale}/group/{id}/users/search", name="search_group_users")
     * @Method("POST")
     *
     * @param int     $id
     * @param Request $request
     *
     * @return Response
     */
    public function searchUsersAction($id, Request $request)
    {
        $group = $this->getGroup($id);

        $query = $request->request->get('query');
        $users = $this->get('app.user_manager')->findUsers($query);

        $notifications = null;
        if ($this->isGranted('EDIT', $group)) {
            $notifications = $this->get('app.notification_manager')->findNotificationsForGroup(
                $this->getUser()->getId(),
                $group->getId()
            );
        }

        return $this->render(
            ':popups:group_users.html.twig',
            [
                'group'         => $group,
                'users'         => $users,
                'notifications' => $notifications,
            ]
        );
    }

    /**
     * @Route("/{_locale}/group/{id}/members/search", name="search_group_members")
     * @Method("POST")
     *
     * @param int     $id
     * @param Request $request
     *
     * @return Response
     */
    public function searchMembersAction($id, Request $request)
    {
        $group = $this->getGroup($id);

        $query = $request->request->get('query');
        $members = $this->get('app.membership_manager')->findGroupMemberships($group->getId(), $query);

        $notifications = null;
        if ($this->isGranted('EDIT', $group)) {
            $notifications = $this->get('app.notification_manager')->findNotificationsForGroup(
                $this->getUser()->getId(),
                $group->getId()
            );
        }

        return $this->render(
            ':popups:group_members.html.twig',
            [
                'group'         => $group,
                'members'       => $members,
                'notifications' => $notifications,
            ]
        );
    }

    /**
     * @param int $id
     *
     * @return Group
     */
    private function getGroup($id)
    {
        $group = $this->get('app.group_manager')->getGroup($id);

        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        return $group;
    }
}
