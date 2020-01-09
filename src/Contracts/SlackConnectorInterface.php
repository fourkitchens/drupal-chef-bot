<?php
/**
 * @file
 * ${fileDescription}
 */

namespace App\Contracts;

use JoliCode\Slack\Api\Model\ObjsChannel;
use JoliCode\Slack\Api\Model\ObjsTeam;
use JoliCode\Slack\Api\Model\ObjsUser;

interface SlackConnectorInterface
{
    /**
     * Unfurl a set of URLs
     *
     * @param array $form_parameters
     * @param array $header_parameters
     * @return \JoliCode\Slack\Api\Model\ChatUnfurlPostResponse200|\JoliCode\Slack\Api\Model\ChatUnfurlPostResponsedefault|\Psr\Http\Message\ResponseInterface|null
     */
    public function chatUnfurl(array $form_parameters = [], array $header_parameters = []);

    /**
     * Unfurl a set of URLs
     *
     * @param array $form_parameters
     * @param array $header_parameters
     * @return \JoliCode\Slack\Api\Model\ChatUnfurlPostResponse200|\JoliCode\Slack\Api\Model\ChatUnfurlPostResponsedefault|\Psr\Http\Message\ResponseInterface|null
     */
    public function chatPostMessage(array $form_parameters = [], array $header_parameters = []);

    /**
     * Gets user info from the slack service.
     *
     * @param string $user_id
     *
     * @return \JoliCode\Slack\Api\Model\ObjsUser
     */
    public function getUser(string $user_id): ObjsUser;

    /**
     * Gets channel information from the slack service.
     *
     * @param $channel_id
     *
     * @return \JoliCode\Slack\Api\Model\ObjsChannel
     */
    public function getChannel($channel_id): ObjsChannel;

    /**
     * Retrieves a team object from slack.
     *
     * @param $team_id
     *
     * @return ObjsTeam
     */
    public function getTeam($team_id): ObjsTeam;
}