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
   * The event dispatcher instance.
   *
   * @var \Illuminate\Events\Dispatcher
   */
  protected $events;

  protected $driver;

  function __construct(AuthTokenDriver $driver, Dispatcher $events)
  {
    $this->driver = $driver;
    $this->events = $events;
  }

  function filter($route, $request) {
    $payload = $request->header('X-Auth-Token');
    $user = $this->driver->validate($payload);
    $this->events->fire('auth.token.valid', $user);
  }
}