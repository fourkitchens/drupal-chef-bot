<?php

namespace App\Utility;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Handle URL Verification requests.
 */
class UrlVerification {

  /**
   * Handle a slack URL challenge request.
   *
   * @param \App\Utility\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A challenge response.
   */
  public static function handle(Request $request) {
    $params = json_decode($request->getContent()) ?? [];
    unset($params->type, $params->token);
    return new JsonResponse($params);
  }

}
