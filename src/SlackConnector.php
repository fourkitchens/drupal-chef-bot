<?php

namespace App;

use App\Contracts\SlackConnectorInterface;
use Doctrine\ORM\Mapping\Entity;
use JoliCode\Slack\Api\Client;
use JoliCode\Slack\Api\Model\ObjsChannel;
use JoliCode\Slack\Api\Model\ObjsTeam;
use JoliCode\Slack\Api\Model\ObjsUser;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class SlackConnector implements SlackConnectorInterface
{

    /**
     * @var Client
     */
    protected $botClient;

    /**
     * @var Client
     */
    protected $userClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructs a SlackConnector object.
     * @param Client $slackBotClient
     * @param Client $slackUserClient
     * @param LoggerInterface $logger
     */
    public function __construct(Client $slackBotClient, Client $slackUserClient, LoggerInterface $logger)
    {
        $this->botClient = $slackBotClient;
        $this->userClient = $slackUserClient;
        $this->logger = $logger;
    }

    /**
     * Unfurl a set of URLs
     *
     * @param array $form_parameters
     * @param array $header_parameters
     * @return \JoliCode\Slack\Api\Model\ChatUnfurlPostResponse200|\JoliCode\Slack\Api\Model\ChatUnfurlPostResponsedefault|\Psr\Http\Message\ResponseInterface|null
     */
    public function chatUnfurl(array $form_parameters = [], array $header_parameters = [])
    {
        return $this->userClient->chatUnfurl($form_parameters, $header_parameters);
    }

    /**
     * Gets user info from the slack service.
     *
     * @param string $user_id
     *
     * @return \JoliCode\Slack\Api\Model\ObjsUser
     */
    public function getUser(string $user_id): ObjsUser {
        $data = $this->userClient->usersInfo(['user' => $user_id]);
        if ($data->getOk()) {
            return $data->getUser();
        }
        $this->logger->error($data->getError());
        throw new \InvalidArgumentException(sprintf("Slack returned an error while trying to retrieve user %s. Error was %s.", $user_id, $data->getError()));
    }

    /**
     * Gets channel information from the slack service.
     *
     * @param $channel_id
     *
     * @return \JoliCode\Slack\Api\Model\ObjsChannel
     */
    public function getChannel($channel_id): ObjsChannel {
        $data = $this->userClient->channelsInfo(['channel' => $channel_id]);
        if ($data->getOk()) {
            return $data->getChannel();
        }
        $this->logger->error($data->getError());
        throw new \InvalidArgumentException(sprintf("Slack returned an error while trying to retrieve channel %s. Error was %s.", $channel_id, $data->getError()));
    }

    /**
     * Retrieves a team object from slack.
     *
     * @param $team_id
     *
     * @return ObjsTeam
     */
    public function getTeam($team_id): ObjsTeam {
        $data = $this->userClient->teamInfo(['team' => $team_id]);
        if ($data->getOk()) {
            return $data->getTeam();
        }
        $this->logger->error($data->getError());
        throw new \InvalidArgumentException(sprintf("Slack returned an error while trying to retrieve channel %s. Error was %s.", $team_id, $data->getError()));
    }

    /**
     * Unfurl a set of URLs
     *
     * @param array $form_parameters
     * @param array $header_parameters
     * @return \JoliCode\Slack\Api\Model\ChatUnfurlPostResponse200|\JoliCode\Slack\Api\Model\ChatUnfurlPostResponsedefault|\Psr\Http\Message\ResponseInterface|null
     */
    public function chatPostMessage(array $form_parameters = [], array $header_parameters = []) {
        $this->botClient->chatPostMessage($form_parameters, $header_parameters);
    }

}
