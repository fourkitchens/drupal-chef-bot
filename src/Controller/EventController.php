<?php

namespace App\Controller;

use App\Entity\EventLog;
use App\Utility\UrlVerification;
use Doctrine\ORM\EntityManagerInterface;
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
   * The event log repository.
   *
   * @var \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface
   */
  protected $eventLogRepository;

  /**
   * The entity manager.
   *
   * @var \Doctrine\ORM\EntityManagerInterface
   */
  protected $entityManager;

  public function __construct(EntityManagerInterface $entity_manager) {
    $this->eventLogRepository = $entity_manager->getRepository(EventLog::class);
    $this->entityManager = $entity_manager;
  }


  /**
   * Receive Slack events.
   *
   * @Rest\Post("/event")
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *
   * @throws \Exception
   */
  public function postEvent(Request $request) {
    $params = json_decode($request->getContent());
    if ($params->type ?? '' === 'event_callback') {
      $event_type = $params->event->type ?? FALSE;
    }
    else {
      $event_type = $params->type ?? FALSE;
    }
    if (!$type) {
      return new Response('', Response::HTTP_BAD_REQUEST);
    }
    if ($type === 'url_verification') {
      return UrlVerification::handle($request);
    }
    return new Response('');
  }
}
