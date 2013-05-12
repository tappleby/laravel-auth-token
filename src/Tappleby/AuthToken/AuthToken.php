<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 3:44 PM
 */

namespace Tappleby\AuthToken;


use Illuminate\Support\Contracts\ArrayableInterface;

class AuthToken implements ArrayableInterface {


  protected $authIdentifier;
  protected $publicKey;
  protected $privateKey;

  function __construct($authIdentifier, $publicKey, $privateKey)
  {
    $this->authIdentifier = $authIdentifier;
    $this->publicKey = $publicKey;
    $this->privateKey = $privateKey;
  }

  public function getAuthIdentifier()
  {
    return $this->authIdentifier;
  }

  public function setAuthIdentifier($authIdentifier)
  {
    $this->authIdentifier = $authIdentifier;
  }

  public function getPrivateKey()
  {
    return $this->privateKey;
  }

  public function getPublicKey()
  {
    return $this->publicKey;
  }

  public function setPrivateKey($privateKey)
  {
    $this->privateKey = $privateKey;
  }

  public function setPublicKey($publicKey)
  {
    $this->publicKey = $publicKey;
  }

  /**
   * Get the instance as an array.
   *
   * @return array
   */
  public function toArray()
  {
    return array(
      'auth_identifier' => $this->authIdentifier,
      'public_key' => $this->publicKey,
      'private_key' => $this->privateKey
    );
  }


}