<?php

namespace App\EventSubscriber;

use App\Contracts\SlackConnectorInterface;
use App\Entity\Channel;
use App\Entity\Message;
use App\Entity\Team;
use App\Entity\User;
use App\Event\SlackEvent;
use App\Repository\ChannelRepository;
use App\Repository\MessageRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\SlackConnector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageLogSubscriber implements EventSubscriberInterface {

    /**
     * The entity manager.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SlackConnectorInterface
     */
    private $slackConnector;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var ChannelRepository
     */
    private $channelRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var MessageRepository
     */
    private $messageRepository;


    public function __construct(EntityManagerInterface $entity_manager, SlackConnectorInterface $connector)
    {
        $this->entityManager = $entity_manager;
        $this->teamRepository = $entity_manager->getRepository(Team::class);
        $this->channelRepository = $entity_manager->getRepository(Channel::class);
        $this->userRepository = $entity_manager->getRepository(User::class);
        $this->messageRepository = $entity_manager->getRepository(Message::class);
        $this->slackConnector = $connector;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents() {
        return [
            SlackEvent::MESSAGE => ['onMessage', 0],
        ];
    }

    public function onMessage(SlackEvent $event_wrapper)
    {
        $event = $event_wrapper->getEvent();
        if ($event->subtype ?? SlackEvent::MESSAGE_SUBTYPE_DEFAULT === SlackEvent::MESSAGE_SUBTYPE_BOT_MESSAGE) {
            return;
        }

        $user_id = $event->user ?? $event->message->user;
        if (!$user_id) {
            throw new \InvalidArgumentException("Could not find user id in message event");
        }

        $team_id = $event_wrapper->getTeamId();
        $channel_id = $event->channel;
        $team = $this->teamRepository->findOneBySlackId($team_id) ?? Team::createFromSlackItem($this->slackConnector->getTeam($team_id));
        $this->entityManager->persist($team);
        $channel = $this->channelRepository->findOneBySlackId($channel_id) ?? Channel::createFromSlackItem($this->slackConnector->getChannel($channel_id));
        $this->entityManager->persist($channel);
        $user = $this->userRepository->findOneBySlackId($user_id) ?? User::createFromSlackItem($this->slackConnector->getUser($user_id), $team);
        $this->entityManager->persist($user);
       // $message = $this->messageRepository->findOneBy(['ts' => $event->ts, 'team' => $team]) ?? new Message();
        // $this->entityManager->persist($message);
       switch ($event->subtype ?? SlackEvent::MESSAGE_SUBTYPE_DEFAULT) {

           case SlackEvent::MESSAGE_SUBTYPE_DEFAULT:
               $parent = null;
               if ($event->thread_ts ?? FALSE) {
                   $parent = $this->messageRepository->findOneBy(['ts' => $event->thread_ts, 'channel' => $channel]);
               }
               $message = Message::create($channel, $user, $event->ts, $event->text, $parent);
               $this->entityManager->persist($message);
               break;

        }
        $this->entityManager->flush();
    }



}
