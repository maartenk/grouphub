<?php

namespace AppBundle\Controller;

use AppBundle\Form\GroupType;
use AppBundle\Model\Collection;
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

        $myGroups = $this->getMyGroups($type, $sortColumn, $sortDirection, $offset, $limit);

        $groupManager = $this->get('app.group_manager');

        $allGroups = new Collection();
        if ($type === null || $type === 'all' || $type === 'all-groups') {
            $allGroups = $groupManager->findGroups(null, null, $offset, $limit, $sortColumn, $sortDirection);
        }

        $searchGroups = new Collection();
        if (!empty($searchQuery) && ($type === null || $type === 'search' || $type === 'results')) {
            $searchGroups = $groupManager->findGroups($searchQuery, null, $offset, $limit, $sortColumn, $sortDirection);
        }

        $memberships = $this->get('app.membership_manager')->findUserMembershipOfGroups(
            $this->getUser()->getId(),
            array_merge($allGroups->toArray(), $searchGroups->toArray())
        );

        return [
            'myGroups'    => $myGroups,
            'allGroups'   => $allGroups,
            'groups'      => $searchGroups,
            'memberships' => $memberships,
            'sort'        => $sort,
            'offset'      => $offset,
            'limit'       => $limit,
            'query'       => $searchQuery,
            'type'        => $type
        ];
    }

    /**
     * @param string $type
     * @param string $sortColumn
     * @param string $sortDirection
     * @param int    $offset
     * @param int    $limit
     *
     * @return Collection
     */
    private function getMyGroups($type, $sortColumn, $sortDirection, $offset, $limit)
    {
        $groupManager = $this->get('app.group_manager');

        if ($type === null) {
            return $groupManager->getMyGroups($this->getUser()->getId(), null, null, $sortColumn, $sortDirection, 0, 4);
        }

        if ($type === 'my') {
            return $groupManager->getMyGroups($this->getUser()->getId(), 'grouphub', null, $sortColumn, $sortDirection, 0, 4);
        }

        if ($type === 'my-owner') {
            return $groupManager->getMyGroups($this->getUser()->getId(), 'grouphub', 'owner', $sortColumn, $sortDirection, $offset, $limit);
        }

        if ($type === 'my-admin') {
            return $groupManager->getMyGroups($this->getUser()->getId(), 'grouphub', 'admin', $sortColumn, $sortDirection, $offset, $limit);
        }

        if ($type === 'my-member') {
            return $groupManager->getMyGroups($this->getUser()->getId(), 'grouphub', 'member', $sortColumn, $sortDirection, $offset, $limit);
        }

        if ($type === 'org') {
            return $groupManager->getMyGroups($this->getUser()->getId(), 'other', null, $sortColumn, $sortDirection, 0, 4);
        }

        if ($type === 'org-owner') {
            return $groupManager->getMyGroups($this->getUser()->getId(), 'other', 'owner', $sortColumn, $sortDirection, $offset, $limit);
        }

        if ($type === 'org-admin') {
            return $groupManager->getMyGroups($this->getUser()->getId(), 'other', 'admin', $sortColumn, $sortDirection, $offset, $limit);
        }

        if ($type === 'org-member') {
            return $groupManager->getMyGroups($this->getUser()->getId(), 'other', 'member', $sortColumn, $sortDirection, $offset, $limit);
        }

        return new Collection();
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
            'my-owner'   => ':groups:my_groups-groups.html.twig',
            'my-admin'   => ':groups:my_groups-groups.html.twig',
            'my-member'  => ':groups:my_groups-groups.html.twig',
            'org'        => ':groups:organisation_groups.html.twig',
            'org-owner'  => ':groups:organisation_groups-groups.html.twig',
            'org-admin'  => ':groups:organisation_groups-groups.html.twig',
            'org-member' => ':groups:organisation_groups-groups.html.twig',
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
