<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 7:34 PM
 */

namespace Tappleby\AuthToken;


class HashProvider {
  private $algo = 'sha256';
  private $hashKey;

  public function getAlgo()
  {
    return $this->algo;
  }

  public function getHashKey()
  {
    return $this->hashKey;
  }

  function __construct($hashKey)
  {
    $this->hashKey = $hashKey;
  }

  public function make($entropy=null)
  {
    if(empty($entropy)) {
      $entropy = $this->generateEntropy();
    }

    return hash($this->algo, $entropy);
  }

  public function makePrivate($publicKey)
  {
    return hash_hmac($this->algo, $publicKey, $this->hashKey);
  }

  public function check($publicKey, $privateKey) {
    $genPublic = $this->makePrivate($publicKey);
    return $genPublic == $privateKey;
  }

  public function generateEntropy() {
    $entropy = mcrypt_create_iv(32, $this->getRandomizer());
    $entropy .= uniqid(mt_rand(), true);

    return $entropy;
  }

  protected function getRandomizer()
  {
    if (defined('MCRYPT_DEV_URANDOM')) return MCRYPT_DEV_URANDOM;

    if (defined('MCRYPT_DEV_RANDOM')) return MCRYPT_DEV_RANDOM;

    mt_srand();

    return MCRYPT_RAND;
  }
}