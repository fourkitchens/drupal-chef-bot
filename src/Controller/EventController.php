<?php

namespace App\Controller;

use App\Entity\EventLog;
use App\Event\SlackEvent;
use App\Utility\UrlVerification;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class EventController
 *
 * @Route("/api", name="api_")
 */
class EventController extends AbstractFOSRestController implements LoggerAwareInterface {

    /**
     * The event log repository.
     *
     * @var \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface
     */
    protected $eventLogRepository;

    /**
     * A Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * The event dispatcher.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */

    protected $dispatcher;

    /**
     * The event to dispatch after the response has been sent.
     *
     * @var \App\Event\SlackEvent
     */
    protected $event;

    /**
     * The event name to dispatch after the response has been sent.
     * @var string
     */
    protected $eventName;

    /**
     * The entity manager.
     *
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entity_manager, EventDispatcherInterface $dispatcher) {
        $this->eventLogRepository = $entity_manager->getRepository(EventLog::class);
        $this->entityManager = $entity_manager;
        $this->dispatcher = $dispatcher;
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
        $request_body_raw = $request->getContent();
        $params = json_decode($request_body_raw);
        if ($params->type ?? '' === 'event_callback') {
            $event_type = $params->event->type ?? FALSE;
        }
        else {
            $event_type = $params->type ?? FALSE;
        }
        if (!$event_type) {
            $this->logger->error("Event Type not found in event callback. Request was {request}", ['request' => $request_body_raw]);
            return new Response('', Response::HTTP_BAD_REQUEST);
        }
        if ($event_type === 'url_verification') {
            $this->logger->debug("Handling URL verification for {request}", ['request' => $request_body_raw]);
            return UrlVerification::handle($request);
        }

        $this->eventName = $event_type;
        $this->event = new SlackEvent($params);
        $this->dispatcher->addListener(KernelEvents::TERMINATE, [$this, 'onKernelTerminate']);

        $this->logger->debug("Handling normal event - {request}", ['request' => $request_body_raw]);
        return new Response('');
    }

    /**
     * Sets the logger.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * Process the event after sending the response.
     *
     * @param \Symfony\Component\HttpKernel\Event\TerminateEvent $event
     */
    public function onKernelTerminate(TerminateEvent $event) {
        $this->logger->debug("Sending slack event {event}", ['event' => $this->eventName]);
        try {
            $this->dispatcher->dispatch($this->event);
            $this->dispatcher->dispatch($this->event, $this->eventName);
        }
        catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

}
