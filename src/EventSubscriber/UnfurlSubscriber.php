<?php

namespace App\EventSubscriber;

use App\Contracts\DrupalConnectorInterface;
use App\Contracts\SlackConnectorInterface;
use App\Event\SlackEvent;
use App\SlackConnector;
use App\Utility\DrupalInfo;
use JoliCode\Slack\Api\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UnfurlSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DrupalConnectorInterface
     */
    protected $drupalConnector;

    /**
     * @var SlackConnectorInterface
     */
     protected $slackConnector;

    /**
     * Constructs a UnfurlSubscriber object.
     *
     * @param DrupalConnectorInterface $drupal_connector
     * @param SlackConnectorInterface $slack_connector
     */
    public function __construct(DrupalConnectorInterface $drupal_connector, SlackConnectorInterface $slack_connector)
    {
        $this->drupalConnector = $drupal_connector;
        $this->slackConnector = $slack_connector;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'link_shared' => 'onLinkShared'
        ];
    }

    /**
     * Handles a link_shared Slack event.
     *
     * @param SlackEvent $event_wrapper
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function onLinkShared(SlackEvent $event_wrapper) {
        $client = new \Hussainweb\DrupalApi\Client();
        $event = $event_wrapper->getEvent();
        $unfurls = [];
        foreach ($event->links as $link) {
            $nid = $this->getNid($link->url);
            $cid = $this->getCid($link->url);
            $this->logger->debug('Nid: {nid}', ['nid' => $nid]);
            if (!$nid) {
                continue;
            }
            $node = $this->drupalConnector->getNode($nid);
            $variables = [
                'url' => $link->url,
                'title' => $node->title,
                'body' => substr(strip_tags($node->body->value), 0, 150) . '...',
                'priority' => $node->field_issue_priority,
                'comment_count' => $node->comment_count,
                'project' => $node->field_project->machine_name,
                'status' => $node->field_issue_status,
            ];
            $variables['tags'] = [];
            foreach ($node->taxonomy_vocabulary_9 as $term) {
                $name = $this->drupalConnector->getTag($term->id);
                $variables['tags'][] = sprintf("<https://www.drupal.org/project/issues/search?issue_tags=%s|%s>", urlencode($name), $name);
            }
            if ($cid) {
                $this->logger->debug('Cid: {cid}', ['cid' => $cid]);
                $comment_delta = 0;
                foreach($node->comments as $delta => $comment_data) {
                    if ($cid == $comment_data->id) {
                        $comment_delta = $delta + 1;
                    }
                }
                $comment = $this->drupalConnector->getComment($cid);
                $variables['comment_delta'] = $comment_delta;
                $variables['comment_author'] = $comment->name;
                $variables['comment_body'] = substr(strip_tags($comment->comment_body->value), 0, 200) . "...";

            }
            $unfurls[$link->url] = ['blocks' => $cid ? $this->issueTemplateWithComment($variables) : $this->issueTemplate($variables)];
        }
        if (!$unfurls) {
            $this->logger->info("Found no links to unfurl");
            return;
        }
        try {
            $this->logger->debug(json_encode($unfurls));
            $result = $this->slackConnector->chatUnfurl([
                'channel' => $event->channel,
                'ts' => $event->message_ts,
                'unfurls' => json_encode($unfurls)
            ]);
            $this->logger->debug("Unfurling Result", (array) $result);
        }
        catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }

    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }


    /**
     * Attempts to find a nid from a d.o. url.
     *
     * @param $url
     * @return bool|string
     */
    protected function getNid($url) {
        $matches = [];
        preg_match('/drupal\\.org\\/(node|project\\/[^\\/]*\\/issues)\\/([0-9]+)(#comment-([0-9]+))?$/', $url, $matches);
        if ($matches) {
            return $matches[2];
        }
        return FALSE;
    }

    /**
     * Attempts to find a cid from a d.o. url.
     *
     * @param $url
     * @return bool|string
     */
    protected function getCid($url) {
        $matches = [];
        preg_match('/drupal\\.org\\/(node|project\\/[^\\/]*\\/issues)\\/([0-9]+)(#comment-([0-9]+))?$/', $url, $matches);
        if ($matches) {
            return $matches[4] ?? FALSE;
        }
        return FALSE;
    }

    /**
     * Provides a filled out issue unfurling template.
     *
     * @param $variables
     * @return array
     */
    protected function issueTemplate($variables) {
        return [
            [
                'type' => 'section',
                'text' => [
                    "type" => "mrkdwn",
                    "text" => sprintf(":drupal: *Drupal.org* - <https://www.drupal.org/project/%s|%s>\n*<%s|%s>*\n%s", $variables['project'], $variables['project'], $variables['url'], $variables['title'], $variables['body']),
                ],
            ],
            ['type' => 'divider'],
            [
                'type' => 'section',
                'fields' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => sprintf("%s *Priority*: %s", DrupalInfo::getPriorityIcon($variables['priority']), DrupalInfo::getPriority($variables['priority'])),
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => sprintf("*Comments*: %s", $variables['comment_count']),
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => sprintf("%s *Status*: %s*", DrupalInfo::getStatusIcon($variables['status']), DrupalInfo::getStatus($variables['status'])),
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => sprintf("*Tags*: %s", implode(', ', $variables['tags'])),
                    ],
                ],
            ],
        ];
    }

    /**
     * Provices an issue template with a comment id.
     *
     * @param $variables
     * @return array
     */
    protected function issueTemplateWithComment($variables) {
        return array_merge($this->issueTemplate($variables), [
                ['type' => 'divider'],
                [
                    'type' => 'section',
                    'text' => [
                        "type" => "mrkdwn",
                        "text" => sprintf("Comment #%s by %s\n%s", $variables['comment_delta'], $variables['comment_author'], $variables['comment_body']),
                    ],
                ],
            ]);
    }

}
