<?php

namespace AppBundle\Controller;

use AppBundle\Model\Membership;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $apiClient = $this->get('app.api_client');

        /** @var Membership[] $memberships */
        $memberships = $apiClient->findUserMemberships(7); // @todo: $this->getUser()->getId());

        // Regroup memberships to make them a little more accessible
        $groups = [];
        foreach ($memberships as $group) {
            $type = $group->getGroup()->getType() === 'grouphub' ? 'grouphub' : 'other';
            $role = $group->getRole();

            $groups[$type][$role][$group->getGroup()->getId()] = $group->getGroup();
        }

        return $this->render('::base.html.twig', [
            'ownerGrouphubGroups'  => isset($groups['grouphub']['owner']) ? $groups['grouphub']['owner'] : [],
            'adminGrouphubGroups'  => isset($groups['grouphub']['admin']) ? $groups['grouphub']['admin'] : [],
            'memberGrouphubGroups' => isset($groups['grouphub']['member']) ? $groups['grouphub']['member'] : [],
            'ownerOtherGroups'     => isset($groups['other']['owner']) ? $groups['other']['owner'] : [],
            'adminOtherGroups'     => isset($groups['other']['admin']) ? $groups['other']['admin'] : [],
            'memberOtherGroups'    => isset($groups['other']['member']) ? $groups['other']['member'] : [],
            'groups'               => $apiClient->findGroups(),
        ]);
    }
}
