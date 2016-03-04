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
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function groupsAction(Request $request)
    {
        $type = $request->query->get('type');
        $query = $request->query->get('query');
        $sort = $request->query->get('sort', 'name');
        $offset = $request->query->get('offset', 0);
        $limit = $request->query->get('limit', 12);

        if (!in_array($sort, ['name', 'timestamp', '-name', '-timestamp'])) {
            throw new BadRequestHttpException();
        }

        return $this->render(
            $this->getTemplate($type),
            $this->getGroups($query, $sort, $offset, $limit, $type)
        );
    }

    /**
     * @param string $searchQuery
     * @param string $sort
     * @param int    $offset
     * @param int    $limit
     * @param string $type
     *
     * @return array
     */
    private function getGroups($searchQuery = '', $sort = 'name', $offset = 0, $limit = 12, $type = null)
    {
        $sortColumn = $sort;
        $sortDirection = 0;
        if ($sort[0] === '-') {
            $sortDirection = 1;
            $sortColumn = substr($sort, 1);
        }

        $groupManager = $this->get('app.group_manager');

        $myGroups = $groupManager->getMyGroups($this->getUser(), $sortColumn, $sortDirection);
        $groups = $groupManager->findGroups($searchQuery, null, $offset, $limit, $sortColumn, $sortDirection);

        $allGroups = $groups;
        if (!empty($searchQuery) && $type === null) {
            $allGroups = $groupManager->findGroups(null, null, $offset, $limit, $sortColumn, $sortDirection);
        }

        return [
            'myGroups'  => $myGroups,
            'allGroups' => $allGroups,
            'groups'    => $groups,
            'sort'      => $sort,
            'offset'    => $offset,
            'limit'     => $limit,
            'query'     => $searchQuery,
        ];
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getTemplate($type)
    {
        if ($type === null) {
            return ':groups.html.twig';
        }

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
