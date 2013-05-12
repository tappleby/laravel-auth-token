<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 10:11 PM
 */

namespace Tappleby\AuthToken;


use Illuminate\Auth\UserProviderInterface;
use Illuminate\Events\Dispatcher;
use Tappleby\AuthToken\Exceptions\NotAuthorizedException;

class AuthTokenFilter {

  /**
   * @var \Tappleby\AuthToken\AuthTokenProviderInterface
   */
  protected $tokens;

  /**
   * @var \Illuminate\Auth\UserProviderInterface
   */
  protected $users;

  /**
   * The event dispatcher instance.
   *
   * @var \Illuminate\Events\Dispatcher
   */
  protected $events;

  function __construct(AuthTokenProviderInterface $tokenProvider, UserProviderInterface $userProvider, Dispatcher $events)
  {
    $this->tokens = $tokenProvider;
    $this->users = $userProvider;
    $this->events = $events;
  }

  function filter($route, $request) {


    $payload = $request->header('X-Auth-Token');

    if($payload == null) {
      throw new NotAuthorizedException();
    }

    $tokenResponse = $this->tokens->find($payload);

    if($tokenResponse == null) {
      throw new NotAuthorizedException();
    }

    $user = $this->users->retrieveByID( $tokenResponse->getAuthIdentifier() );

    if($user == null) {
      throw new NotAuthorizedException();
    }

    $this->events->fire('auth.token.valid', $user);
  }
}