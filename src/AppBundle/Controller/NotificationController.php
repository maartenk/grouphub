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
     * @Route("/notification/{id}/confirm", name="notification_confirm")
     * @Method("POST")
     *
     * @param int $id
     *
     * @return Response
     */
    public function confirmAction($id)
    {
        // @todo: check if allowed

        $this->get('app.notification_manager')->confirmNotification($this->getUser()->getId(), $id);

        return new Response();
    }

    /**
     * @Route("/notification/{id}/deny", name="notification_deny")
     * @Method("POST")
     *
     * @param int $id
     *
     * @return Response
     */
    public function denyAction($id)
    {
        // @todo: check if allowed

        $this->get('app.notification_manager')->denyNotification($this->getUser()->getId(), $id);

        return new Response();
    }

    /**
     * @Route("/notifications", name="notifications")
     * @Method("GET")
     *
     * @return Response
     */
    public function listAction()
    {
        $notifications = $this->get('app.notification_manager')->findNotifications($this->getUser()->getId());

        return $this->render(':popups:notifications.html.twig', ['notifications' => $notifications]);
    }

    /**
     * @Route("/notification/count", name="notification_count")
     * @Method("GET")
     *
     * @return Response
     */
    public function countAction()
    {
        $notifications = $this->get('app.notification_manager')->findNotifications($this->getUser()->getId());

        return new Response(count($notifications));
    }
}
