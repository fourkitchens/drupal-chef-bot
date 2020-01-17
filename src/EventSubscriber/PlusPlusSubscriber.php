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
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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

    /**
     * The plusplus cache.
     *
     * This cache is used to store the most recent plusplus for a channel.
     * @var CacheInterface;
     */
    protected $cache;

    protected $text;

    protected $current_count;

    protected $botname;

    protected $channel_id = '';

    protected $isThreaded = FALSE;

    protected $thread_ts;

    protected $for = '';

    protected $original_ts;


    public function __construct(EntityManagerInterface $entity_manager, SlackConnectorInterface $connector, CacheInterface $plusPlusCache, string $slackBotname)
    {
        $this->entityManager = $entity_manager;
        $this->teamRepository = $entity_manager->getRepository(Team::class);
        $this->channelRepository = $entity_manager->getRepository(Channel::class);
        $this->userRepository = $entity_manager->getRepository(User::class);
        $this->messageRepository = $entity_manager->getRepository(Message::class);
        $this->slackConnector = $connector;
        $this->cache = $plusPlusCache;
        $this->botname = $slackBotname;
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
     * Respond to a message event.
     *
     * @param SlackEvent $event_wrapper
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function onMessage(SlackEvent $event_wrapper)
    {

        $event = $event_wrapper->getEvent();
        $team = $team = $this->teamRepository->findOneBySlackId($event_wrapper->getTeamId());
        if (($event->subtype ?? SlackEvent::MESSAGE_SUBTYPE_DEFAULT) === SlackEvent::MESSAGE_SUBTYPE_DEFAULT) {
            $this->text = $event->text ?? '';
            $this->current_count = 1;
            $this->channel_id = $event->channel;
            if ($event->thread_ts ?? FALSE) {
                $this->thread_ts = $event->thread_ts;
                $this->isThreaded = TRUE;
            }
            if ($this->text === '++') {
                $cache = $this->getPreviousPlusPlus();
                if (!$cache->text) {
                    $this->slackConnector->chatPostMessage([
                        'text' => BotResponse::PLUSPLUS_NOT_FOUND,
                        'channel' => $event->channel,
                        'thread_ts' => $event->thread_ts ?? '',
                    ]);
                    return;
                }
                $this->text = $cache->text;
                $this->current_count = $cache->count + 1;
                $this->original_ts = $cache->original_ts;
            }
            else {
                $this->current_count = 1;
                $this->original_ts = $event->thread_ts ?? $event->ts;
            }
            $matches = [];
            preg_match_all('/<@([^>]+)>\s?(\+\+|\+=([0-9]+)?)/', $this->text, $matches);
            if ($matches) {
                $this->for = BotResponse::getFor($this->text);
                foreach($matches[1] as $delta => $user_id) {

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
                    $user->setPoints($user->getPoints() + $matches[3][$delta] ?? 1);
                    $this->current_count += ($matches[3][$delta] ?? 1) - 1;
                    $this->respondOne($user);

                }
                if ($this->isThreaded) {
                    $this->cache->get(sprintf('%s-%s', $this->channel_id, $this->thread_ts), [$this, 'setCache'], INF);
                    $this->cache->get(sprintf('%s-%s', $this->channel_id, $this->original_ts), [$this, 'setCache'], INF);
                }
                else {
                    $this->cache->get($this->channel_id, [$this, 'setCache'], INF);
                    $this->cache->get(sprintf('%s-%s', $this->channel_id, $event->ts), [$this, 'setCache'], INF);
                    $this->cache->get(sprintf('%s-%s', $this->channel_id, $this->original_ts), [$this, 'setCache'], INF);

                }

            }
        }
        $this->entityManager->flush();
    }

    /**
     * Populates a cache item with data from the current request.
     *
     * @param ItemInterface $item
     * @return object
     */
    public function setCache(ItemInterface $item) {
        $item->expiresAfter(60*60*72);
        return (object) [
            'text' => $this->text,
            'count' => $this->current_count,
            'original_ts' => $this->original_ts,
        ];
    }

    /**
     * Respond to a single user being ++ed
     *
     * @param User $user
     */
    protected function respondOne(User $user) {
        $points = $user->getPoints();
        $points .= ($points === 1) ? ' point' : ' points';
        if ($user->getName() === $this->botname) {
            $text = sprintf(BotResponse::PLUSPLUS_BOT, $points);
        }
        else if ($this->for) {
            $text = sprintf(BotResponse::PLUSPLUS_FOR, $user->getRealName(), $points, $this->current_count, ($this->current_count == 1) ? 'is' : 'are', $this->for);
        }
        else {
            $text = sprintf(BotResponse::PLUSPLUS, $user->getRealName(), $points);
        }
        $data = [
            'channel' => $this->channel_id,
            'text' => $text,
        ];
        if ($this->isThreaded) {
            $data['thread_ts'] = $this->thread_ts;
        }
        $this->slackConnector->chatPostMessage($data);

    }

    /**
     * Finds the original message on a '++'
     *
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getPreviousPlusPlus() {
        if ($this->isThreaded) {
            $key = sprintf('%s-%s', $this->channel_id, $this->thread_ts);
        }
        else {
            $key = $this->channel_id;
        }
        $data = $this->cache->get($key, [self::class, 'blankCache']);
        if ($data->original_ts) {
            return $this->cache->get(sprintf('%s-%s', $this->channel_id, $data->original_ts), [self::class, 'blankCache']);
        }
        return $data;
    }

    /**
     * Returns an empty cache object.
     *
     * @param $e
     * @param $s
     * @return object
     */
    public static function blankCache($e, &$s) {
        $s = false;
        return (object) ['text' => '', 'count' => 0, 'original_ts' => ''];
    }
}
