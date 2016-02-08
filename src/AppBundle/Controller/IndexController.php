<?php

namespace AppBundle\Controller;

use AppBundle\Form\GroupType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IndexController
 *
 * @todo: refactor, also reorder templates
 */
class IndexController extends Controller
{
    /**
     * @Route("/{_locale}", defaults={"_locale": "en"}, requirements={"_locale": "en|nl"}, name="home")
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

        return $this->render('::base.html.twig', [
            'ownerGrouphubGroups'  => isset($groups['grouphub']['owner']) ? $groups['grouphub']['owner'] : [],
            'adminGrouphubGroups'  => isset($groups['grouphub']['admin']) ? $groups['grouphub']['admin'] : [],
            'memberGrouphubGroups' => isset($groups['grouphub']['member']) ? $groups['grouphub']['member'] : [],
            'ownerOtherGroups'     => isset($groups['other']['owner']) ? $groups['other']['owner'] : [],
            'adminOtherGroups'     => isset($groups['other']['admin']) ? $groups['other']['admin'] : [],
            'memberOtherGroups'    => isset($groups['other']['member']) ? $groups['other']['member'] : [],
            'groups'               => $this->get('app.group_manager')->findGroups(),
            'add_form'             => $addForm->createView(),
        ]);
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

        return $this->render(':groups:search-results.html.twig', [
            'ownerGrouphubGroups'  => isset($groups['grouphub']['owner']) ? $groups['grouphub']['owner'] : [],
            'adminGrouphubGroups'  => isset($groups['grouphub']['admin']) ? $groups['grouphub']['admin'] : [],
            'memberGrouphubGroups' => isset($groups['grouphub']['member']) ? $groups['grouphub']['member'] : [],
            'ownerOtherGroups'     => isset($groups['other']['owner']) ? $groups['other']['owner'] : [],
            'adminOtherGroups'     => isset($groups['other']['admin']) ? $groups['other']['admin'] : [],
            'memberOtherGroups'    => isset($groups['other']['member']) ? $groups['other']['member'] : [],
            'groups'               => $this->get('app.group_manager')->findGroups($query),
            'query'                => $query,
        ]);
    }

    /**
     * @Route("/add_group", name="add_group")
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
            $this->get('app.api_client')->addGroup($form->getData());

            return $this->redirect($this->generateUrl('home'));
        }

        return new Response($form->getErrors(true));
    }
}
