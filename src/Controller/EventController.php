<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class EventController
 *
 * @Route("/api", name="api_")
 */
class EventController extends AbstractFOSRestController {

  /**
   * Receive Slack events.
   *
   * @Rest\Post("/event")
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function postEvent(Request $request) {
    $params = json_decode($request->getContent());
    if ($params->type === 'url_verification') {
      return new Response(json_encode(['challenge' => $params->challenge]));
    }
    return new Response('');
  }
}
