<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 6:17 PM
 */

use Mockery as m;

class DatabaseAuthTokenProviderTest extends PHPUnit_Framework_TestCase {

  public function tearDown()
  {
    m::close();
  }

  /**
   * @param null|\Illuminate\Encryption\Encrypter $enc
   * @param array $encPayload
   * @return \Tappleby\AuthToken\DatabaseAuthTokenProvider
   */
  private function getProvider( $enc = null,  $encPayload = array('id' => 1, 'key' => 'public') ) {
    $conn = m::mock('Illuminate\Database\Connection');

    if(!$enc) {
      m::mock('Illuminate\Encryption\Encrypter');
    }

    $provider = new Tappleby\AuthToken\DatabaseAuthTokenProvider($conn, 'table', $enc, m::mock('\Tappleby\AuthToken\HashProvider'));

    return $provider;
  }

  public function testCreateAuthTokenInvalidUser()
  {
    $enc =  m::mock('Illuminate\Encryption\Encrypter');
    $user = m::mock('Illuminate\Auth\UserInterface');
    $user->shouldReceive('getAuthIdentifier')->once()->andReturnNull();

    $provider = $this->getProvider( $enc );


    $token = $provider->create($user);
    $this->assertFalse( $token );
  }

  public function testCreateAuthToken()
  {
    $enc =  m::mock('Illuminate\Encryption\Encrypter');
    $user = m::mock('Illuminate\Auth\UserInterface');
    $user->shouldReceive('getAuthIdentifier')->twice()->andReturn("foo");

    $provider = $this->getProvider( $enc );

    $provider->getHasher()->shouldReceive('make')->once()->andReturn('public');
    $provider->getHasher()->shouldReceive('makePrivate')->once()->andReturn('private');

    $provider->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));
    $query->shouldReceive('insert')->once();


    $token = $provider->create($user);
    $this->assertInstanceOf('\Tappleby\AuthToken\AuthToken', $token);
    $this->assertEquals('foo', $token->getAuthIdentifier());
    $this->assertEquals('public', $token->getPublicKey() );
    $this->assertEquals('private', $token->getPrivateKey() );
  }

  public function testFindReturnsNullWhenPayloadIsInvalid()
  {
    $enc =  m::mock('Illuminate\Encryption\Encrypter');
    $enc->shouldReceive('decrypt')->once()->andReturnNull();

    $provider = $this->getProvider( $enc );

    $this->assertNull($provider->find(null));
  }

  public function testFindReturnsNullWhenPayloadIsMissingParam()
  {
    $enc =  m::mock('Illuminate\Encryption\Encrypter');
    $enc->shouldReceive('decrypt')->twice()->andReturn(array('id' => 1), array('key' => 1));

    $provider = $this->getProvider( $enc );
    $this->assertNull($provider->find('payload')); // ID Only
    $this->assertNull($provider->find('payload')); // Key Only
  }

  public function testFindReturnsNullWhenVerifyFailed()
  {
    $enc =  m::mock('Illuminate\Encryption\Encrypter');
    $enc->shouldReceive('decrypt')->once()->andReturn(array('id' => 1, 'key' => 'public'));

    $provider = $this->getProvider( $enc );
    $provider->getHasher()->shouldReceive('makePrivate')->once()->andReturn('private');
    $provider->getHasher()->shouldReceive('check')->once()->andReturn(false);

    $this->assertNull($provider->find('payload'));
  }

  public function testFindReturnsNullWhenTokenNotFound()
  {

    $mockData = (object)array(
      'authId' => 1,
      'public' => 'public',
      'private' => 'private'
    );

    $enc =  m::mock('Illuminate\Encryption\Encrypter');
    $enc->shouldReceive('decrypt')->once()->andReturn(array('id' => $mockData->authId, 'key' => $mockData->public));

    $provider = $this->getProvider( $enc );
    $provider->getHasher()->shouldReceive('makePrivate')->once()->andReturn($mockData->private);
    $provider->getHasher()->shouldReceive('check')->once()->andReturn(true);

    $provider->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));

    $query->shouldReceive('where')->once()->with('auth_identifier', $mockData->authId)->andReturn($query);
    $query->shouldReceive('where')->once()->with('public_key', $mockData->public)->andReturn($query);
    $query->shouldReceive('where')->once()->with('private_key', $mockData->private)->andReturn($query);
    $query->shouldReceive('first')->once()->andReturnNull();

    $this->assertNull($provider->find('payload'));
  }

  public function testFindReturnsValidAuthToken()
  {
    $mockData = (object)array(
      'authId' => 1,
      'public' => 'public',
      'private' => 'private'
    );

    $enc =  m::mock('Illuminate\Encryption\Encrypter');
    $enc->shouldReceive('decrypt')->once()->andReturn(array('id' => $mockData->authId, 'key' => $mockData->public));

    $provider = $this->getProvider( $enc );
    $provider->getHasher()->shouldReceive('makePrivate')->once()->andReturn($mockData->private);
    $provider->getHasher()->shouldReceive('check')->once()->andReturn(true);

    $provider->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));

    $query->shouldReceive('where')->once()->with('auth_identifier', $mockData->authId)->andReturn($query);
    $query->shouldReceive('where')->once()->with('public_key', $mockData->public)->andReturn($query);
    $query->shouldReceive('where')->once()->with('private_key', $mockData->private)->andReturn($query);
    $query->shouldReceive('first')->once()->andReturn(array('auth_identifier' => $mockData->authId,
                                                            'public_key' => $mockData->public,
                                                            'private_key' => $mockData->private));

    $token = $provider->find('payload');

    $this->assertNotNull($token);
    $this->assertInstanceOf('\Tappleby\AuthToken\AuthToken', $token);
    $this->assertEquals( $mockData->authId, $token->getAuthIdentifier() );
    $this->assertEquals( $mockData->public, $token->getPublicKey() );
    $this->assertEquals( $mockData->private, $token->getPrivateKey() );
  }

  public function testPurgeGetsIdentifierFromUser() {
    $enc =  m::mock('Illuminate\Encryption\Encrypter');
    $user = m::mock('Illuminate\Auth\UserInterface');
    $user->shouldReceive('getAuthIdentifier')->once()->andReturn(1);

    $provider = $this->getProvider( $enc );

    $provider->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));
    $query->shouldReceive('where')->once()->with('auth_identifier', 1)->andReturn($query);
    $query->shouldReceive('delete')->once()->andReturn(0);

    $provider->purge( $user );
  }

  public function testPurgeReturnsFalseWhenNoTokensDeleted() {
    $enc =  m::mock('Illuminate\Encryption\Encrypter');
    $provider = $this->getProvider( $enc );

    $provider->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));
    $query->shouldReceive('where')->once()->with('auth_identifier', 1)->andReturn($query);
    $query->shouldReceive('delete')->once()->andReturn(0);

    $this->assertFalse( $provider->purge(1) );
  }

  public function testPurgeReturnsTrueWhenTokensDeleted() {
    $enc =  m::mock('Illuminate\Encryption\Encrypter');
    $provider = $this->getProvider( $enc );

    $provider->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));
    $query->shouldReceive('where')->once()->with('auth_identifier', 1)->andReturn($query);
    $query->shouldReceive('delete')->once()->andReturn(5);

    $this->assertTrue( $provider->purge(1) );
  }
}