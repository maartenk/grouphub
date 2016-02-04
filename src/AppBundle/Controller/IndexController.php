<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class IndexController
 */
class IndexController extends Controller
{
    /**
     * @Route("/{_locale}", defaults={"_locale": "en"}, requirements={"_locale": "en|nl"}, name="home")
     */
    public function indexAction()
    {
        $groups = $this->get('app.group_manager')->getMyGroups($this->getUser());

        return $this->render('::base.html.twig', [
            'ownerGrouphubGroups'  => isset($groups['grouphub']['owner']) ? $groups['grouphub']['owner'] : [],
            'adminGrouphubGroups'  => isset($groups['grouphub']['admin']) ? $groups['grouphub']['admin'] : [],
            'memberGrouphubGroups' => isset($groups['grouphub']['member']) ? $groups['grouphub']['member'] : [],
            'ownerOtherGroups'     => isset($groups['other']['owner']) ? $groups['other']['owner'] : [],
            'adminOtherGroups'     => isset($groups['other']['admin']) ? $groups['other']['admin'] : [],
            'memberOtherGroups'    => isset($groups['other']['member']) ? $groups['other']['member'] : [],
            'groups'               => $this->get('app.group_manager')->findGroups(),
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
}
