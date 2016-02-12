<?php

namespace AppBundle\Controller;

use AppBundle\Form\GroupType;
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
            $apiClient = $this->get('app.api_client');

            $group = $apiClient->addGroup($form->getData());
            $this->get('app.grouphub_ldap_client')->addGroup($group);
            $apiClient->updateGroupReference($group->getId(), $group->getReference());

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
        $group = $this->get('app.group_manager')->getGroup($id);

        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        $members = $this->get('app.api_client')->findGroupMemberships($group->getId());
        $users = $this->get('app.api_client')->findUsers();

        return $this->render(
            ':popups:group_details.html.twig',
            [
                'group'   => $group,
                'members' => $members,
                'users'   => $users // @todo: find not-members or simply show edit actions for existing members
            ]
        );
    }
}
