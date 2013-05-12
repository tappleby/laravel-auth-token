<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 9:23 PM
 */

namespace Tappleby\AuthToken;


use Illuminate\Auth\GenericUser;

class AuthTokenGuard {
  /**
   * @var \Tappleby\AuthToken\AuthTokenProviderInterface
   */
  protected $tokenProvider;

  function __construct($tokenProvider)
  {
    $this->tokenProvider = $tokenProvider;
  }

  /**
   * @return \Tappleby\AuthToken\AuthTokenProviderInterface
   */
  public function getProvider()
  {
    return $this->tokenProvider;
  }
}