<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * NotificationController
 */
class NotificationController extends Controller
{
    /**
     * @Route("/notification/{id}", name="notification_process")
     * @Method("POST")
     *
     * @param int $id
     *
     * @return Response
     */
    public function processAction($id)
    {
        $this->get('app.notification_manager')->processNotification($this->getUser()->getId(), $id);

        return new Response();
    }
}
