<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class SlackEvent extends Event {

    public const APP_MENTION = 'app_mention';
    public const MESSAGE = 'message';

    // Message subtypes
    public const MESSAGE_SUBTYPE_MESSAGE_CHANGED = 'message_changed';
    public const MESSAGE_SUBTYPE_MESSAGE_DELETED = 'message_deleted';
    public const MESSAGE_SUBTYPE_DEFAULT = 'message_default';
    public const MESSAGE_SUBTYPE_BOT_MESSAGE = 'bot_message';
    public const MESSAGE_SUBTYPE_MESSAGE_REPLIED = 'message_replied';
    public const MESSAGE_SUBTYPE_THREAD_BROADCAST = 'thread_broadcast';

    /**
     * The team id.
     *
     * @var string
     */
    protected $teamId;

    /**
     * The app id.
     *
     * @var string
     */
    protected $appId;

    /**
     * The event object.
     *
     * @var object
     */
    protected $event;

    /**
     * The event type.
     *
     * @var string
     */
    protected $type;

    /**
     * List of user ids inclded with the event.
     *
     * @var string[]
     */
    protected $authedUsers;

    /**
     * The unique slack event id.
     *
     * @var string
     */
    protected $eventId;

    /**
     * The event timestamp.
     *
     * @var string
     */
    protected $eventTime;

    /**
     * Constructs a SlackEvent object.
     *
     * @param object $event_wrapper
     *   The event wrapper object.
     */
    public function __construct($event_wrapper) {
        if (empty($event_wrapper->event)) {
            throw new \InvalidArgumentException("Event object does not exist");
        }
        $this->teamId = $event_wrapper->team_id ?? '';
        $this->appId = $event_wrapper->api_app_id ?? '';
        $this->event = $event_wrapper->event;
        $this->type = $event_wrapper->event->type ?? '';
        $this->authedUsers = $event_wrapper->authed_users ?? [];
        $this->eventId = $event_wrapper->event_id ?? '';
        $this->eventTime = $event_wrapper->event_time ?? '';
    }

    /**
     * Gets the value of $TeamId.
     *
     * @return string
     */
    public function getTeamId(): string {
        return $this->teamId;
    }

    /**
     * Gets the value of $AppId.
     *
     * @return string
     */
    public function getAppId(): string {
        return $this->appId;
    }

    /**
     * Gets the value of $Event.
     *
     * @return object
     */
    public function getEvent(): object {
        return $this->event;
    }

    /**
     * Gets the value of $Type.
     *
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * Gets the value of $AuthedUsers.
     *
     * @return string[]
     */
    public function getAuthedUsers(): array {
        return $this->authedUsers;
    }

    /**
     * Gets the value of $EventId.
     *
     * @return string
     */
    public function getEventId(): string {
        return $this->eventId;
    }

    /**
     * Gets the value of $EventTime.
     *
     * @return string
     */
    public function getEventTime(): string {
        return $this->eventTime;
    }

}
