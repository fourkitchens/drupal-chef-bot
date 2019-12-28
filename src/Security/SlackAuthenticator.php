<?php

namespace App\Security;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Slack Api Authenticator
 */
class SlackAuthenticator extends AbstractGuardAuthenticator {

  private const SLACK_VERSION = 'v0';

  private $signingSecret;

  public function __construct(ContainerBagInterface $params) {
    $this->signingSecret = $params->get('slack.signing_secret');
  }


  /**
   * @inheritDoc
   */
  public function start(Request $request, AuthenticationException $authException = NULL) {
    return new Response('', Response::HTTP_UNAUTHORIZED);
  }

  /**
   * @inheritDoc
   */
  public function supports(Request $request) {
    return $request->headers->has('X-Slack-Signature');
  }

  /**
   * @inheritDoc
   */
  public function getCredentials(Request $request) {
    return [
      'signature' => $request->headers->get('X-Slack-Signature'),
      'timestamp' => $request->headers->get('X-Slack-Request-Timestamp'),
      'body' => $request->getContent(),
    ];
  }

  /**
   * @inheritDoc
   */
  public function getUser($credentials, UserProviderInterface $userProvider) {
    return new AuthUser('slack', ['ROLE_SLACK']);
  }

  /**
   * @inheritDoc
   */
  public function checkCredentials($credentials, UserInterface $user) {
    return abs(time() - $credentials['timestamp']) < 300 && $credentials['signature'] === 'v0=' . hash_hmac('sha256', sprintf('%s:%s:%s', static::SLACK_VERSION, $credentials['timestamp'], $credentials['body']), $this->signingSecret);
  }

  /**
   * @inheritDoc
   */
  public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
    return new Response('', Response::HTTP_UNAUTHORIZED);
  }

  /**
   * @inheritDoc
   */
  public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
  }

  /**
   * @inheritDoc
   */
  public function supportsRememberMe() {
    return false;
  }
}
