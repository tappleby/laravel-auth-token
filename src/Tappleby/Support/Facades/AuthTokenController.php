<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 11:28 PM
 */

namespace Tappleby\Support\Facades;


use Illuminate\Support\Facades\Facade;

class AuthTokenController extends Facade {
  protected static function getFacadeAccessor() { return 'tappleby.auth.token.controller'; }

  public function callAction($container, $router, $method, $parameters)
  {
    $instance = static::resolveFacadeInstance(static::getFacadeAccessor());
    return $instance->callAction($container, $router, $method, $parameters);
  }
}