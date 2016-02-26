<?php

namespace AppBundle\Controller;

use AppBundle\Form\GroupType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class IndexController
 */
class IndexController extends Controller
{
    /**
     * @Route("/{_locale}", defaults={"_locale": "en"}, requirements={"_locale": "en|nl"}, name="home")
     * @Method("GET")
     *
     * @return Response
     */
    public function indexAction()
    {
        $addForm = $this->createForm(
            GroupType::class,
            null,
            [
                'action' => $this->generateUrl('add_group'),
            ]
        );

        return $this->render(
            '::base.html.twig',
            array_merge(
                $this->getGroups(),
                [
                    'add_form'      => $addForm->createView(),
                    'notifications' => $this->get('app.notification_manager')->findNotifications(
                        $this->getUser()->getId()
                    ),
                ]
            )
        );
    }

    /**
     * @Route("/{_locale}/groups", defaults={"_locale": "en"}, requirements={"_locale": "en|nl"}, name="groups")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function groupsAction(Request $request)
    {
        $type = $request->get('type', 'results');
        $query = $request->get('query');
        $sort = $request->get('sort', 'name');
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 5);

        if (!in_array($sort, ['name', 'timestamp', '-name', '-timestamp'])) {
            throw new BadRequestHttpException();
        }

        return $this->render(
            $this->getTemplate($type),
            $this->getGroups($query, $sort, $offset, $limit)
        );
    }

    /**
     * @param string $searchQuery
     * @param string $sort
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    private function getGroups($searchQuery = '', $sort = 'name', $offset = 0, $limit = 5)
    {
        $sortColumn = $sort;
        $sortDirection = 0;
        if ($sort[0] === '-') {
            $sortDirection = 1;
            $sortColumn = substr($sort, 1);
        }

        $myGroups = $this->get('app.group_manager')->getMyGroups($this->getUser(), $sortColumn, $sortDirection);
        $groups = $this->get('app.group_manager')->findGroups($searchQuery, null, $offset, $limit, $sortColumn, $sortDirection);

        return [
            'ownerGrouphubGroups'    => isset($myGroups['grouphub']['owner']) ? $myGroups['grouphub']['owner'] : [],
            'adminGrouphubGroups'    => isset($myGroups['grouphub']['admin']) ? $myGroups['grouphub']['admin'] : [],
            'memberGrouphubGroups'   => isset($myGroups['grouphub']['member']) ? $myGroups['grouphub']['member'] : [],
            'prospectGrouphubGroups' => isset($myGroups['grouphub']['prospect']) ? $myGroups['grouphub']['prospect'] : [],
            'ownerOtherGroups'       => isset($myGroups['other']['owner']) ? $myGroups['other']['owner'] : [],
            'adminOtherGroups'       => isset($myGroups['other']['admin']) ? $myGroups['other']['admin'] : [],
            'memberOtherGroups'      => isset($myGroups['other']['member']) ? $myGroups['other']['member'] : [],
            'groups'                 => $groups,
            'sort'                   => $sort,
            'offset'                 => $offset,
            'limit'                  => $limit,
            'query'                  => $searchQuery,
        ];
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getTemplate($type)
    {
        $mapping = [
            'my'         => ':groups:my_groups.html.twig',
            'org'        => ':groups:organisation_groups.html.twig',
            'all'        => ':groups:all_groups.html.twig',
            'all-groups' => ':groups:all_groups-groups.html.twig',
            'search'     => ':groups:search.html.twig',
            'results'    => ':groups:search-results.html.twig',
        ];

        if (!array_key_exists($type, $mapping)) {
            throw new BadRequestHttpException();
        }

        return $mapping[$type];
    }
}
