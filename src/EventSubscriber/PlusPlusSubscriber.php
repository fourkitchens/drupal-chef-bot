<?php

namespace App\EventSubscriber;

use App\BotResponse;
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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlusPlusSubscriber implements EventSubscriberInterface
{


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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SlackEvent::MESSAGE => ['onMessage', 10],
        ];
    }

    /**
     * Respond to a message event
     *
     * @param SlackEvent $event_wrapper
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function onMessage(SlackEvent $event_wrapper)
    {
        $event = $event_wrapper->getEvent();
        $team = $team = $this->teamRepository->findOneBySlackId($event_wrapper->getTeamId());
        if ($event->subtype ?? SlackEvent::MESSAGE_SUBTYPE_DEFAULT === SlackEvent::MESSAGE_SUBTYPE_DEFAULT) {
            $text = $event->text ?? '';
            $matches = [];
            preg_match_all('/<@([^>]+)>\s?\+\+/', $text, $matches);
            if ($matches) {
                foreach($matches[1] as $user_id) {

                    $user = $this->userRepository->findOneBySlackId($user_id) ?? User::createFromSlackItem($this->slackConnector->getUser($user_id), $team);
                    $this->entityManager->persist($user);

                    if ($user_id === $event->user) {
                        $this->slackConnector->chatPostMessage([
                            'text' => sprintf(BotResponse::PLUSPLUS_YOURSELF, $user_id, $user->getPoints()),
                            'channel' => $event->channel,
                            'thread_ts' => $event->thread_ts ?? '',
                        ]);
                        continue;
                    }
                    $user->setPoints($user->getPoints() + 1);

                    if ($user->getName() === 'drupalchef') {
                        $this->slackConnector->chatPostMessage([
                            'text' => sprintf(BotResponse::PLUSPLUS_BOT, $user->getPoints()),
                            'channel' => $event->channel,
                            'thread_ts' => $event->thread_ts ?? '',
                        ]);
                    }
                    else {
                        $this->slackConnector->chatPostMessage([
                            'text' => sprintf(BotResponse::PLUSPLUS, $user->getRealName(), $user->getPoints()),
                            'channel' => $event->channel,
                            'thread_ts' => $event->thread_ts ?? '',
                        ]);
                    }
                }
            }
            if ($text === '++') {
                $this->slackConnector->chatPostMessage([
                    'text' => sprintf(BotResponse::IN_PROGRESS, $event->user),
                    'channel' => $event->channel,
                    'thread_ts' => $event->thread_ts ?? '',
                ]);
            }
        }
        $this->entityManager->flush();
    }

}
