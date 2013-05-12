<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 9:01 PM
 */

class HashProviderTest extends PHPUnit_Framework_TestCase {

  public function testMakeWithoutParams()
  {
    $h = new \Tappleby\AuthToken\HashProvider('key');
    $this->assertNotEmpty($h->make());
  }

  public function testMakePrivate()
  {
    $h = new \Tappleby\AuthToken\HashProvider('key');
    $this->assertNotEmpty($h->makePrivate( $h->make() ));
  }

  public function testCheckInvalidKeyPair()
  {
    $h = new \Tappleby\AuthToken\HashProvider('key');

    $this->assertFalse($h->check( 'good', 'bad' ));
  }

  public function testCheckValidKeyPair() {
    $h = new \Tappleby\AuthToken\HashProvider('key');

    $pub = $h->make();
    $priv = $h->makePrivate($pub);

    $this->assertTrue( $h->check($pub, $priv) );
  }
}