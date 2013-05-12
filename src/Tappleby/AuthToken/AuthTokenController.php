<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 11:26 PM
 */

namespace Tappleby\AuthToken;


class AuthTokenController {
  /**
   * @var \Tappleby\AuthToken\AuthTokenProviderInterface
   */
  protected $tokens;

  /**
   * @var \Illuminate\Auth\UserProviderInterface
   */
  protected $users;

  function __construct(AuthTokenProviderInterface $tokenProvider, UserProviderInterface $userProvider)
  {
    $this->tokens = $tokenProvider;
    $this->users = $userProvider;
  }
}