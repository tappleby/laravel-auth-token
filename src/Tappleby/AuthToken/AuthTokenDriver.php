<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 9:23 PM
 */

namespace Tappleby\AuthToken;

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
use Tappleby\AuthToken\Exceptions\NotAuthorizedException;

class AuthTokenDriver {
  /**
   * @var \Tappleby\AuthToken\AuthTokenProviderInterface
   */
  protected $tokens;

  /**
   * @var \Illuminate\Auth\UserProviderInterface
   */
  protected $users;

  function __construct(AuthTokenProviderInterface $tokens, UserProviderInterface $users)
  {
    $this->tokens = $tokens;
    $this->users = $users;
  }

  /**
   * @return \Tappleby\AuthToken\AuthTokenProviderInterface
   */
  public function getProvider()
  {
    return $this->tokens;
  }



  public function validate($authTokenPayload) {

    if($authTokenPayload == null) {
      throw new NotAuthorizedException();
    }

    $tokenResponse = $this->tokens->find($authTokenPayload);

    if($tokenResponse == null) {
      throw new NotAuthorizedException();
    }

    $user = $this->users->retrieveByID( $tokenResponse->getAuthIdentifier() );

    if($user == null) {
      throw new NotAuthorizedException();
    }

    return $user;
  }

  public function attempt(array $credentials) {
    $user = $this->users->retrieveByCredentials($credentials);

    if($user instanceof UserInterface && $this->users->validateCredentials($user, $credentials)) {
      return $this->tokens->create($user);
    }

    return false;
  }
}