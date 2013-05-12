<?php
/*
 * User: tappleby
 * Date: 2013-05-12
 * Time: 12:33 AM
 */

use Mockery as m;

class AuthTokenDriverTest extends PHPUnit_Framework_TestCase {

  public function tearDown()
  {
    m::close();
  }

  public function setUp() {
    m::mock('Illuminate\Auth\UserInterface');
  }

  public function testValidateReturnsFalseNullToken() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');

    $driver = new \Tappleby\AuthToken\AuthTokenDriver($tokens, $users);


    $this->assertFalse( $driver->validate(null) );
  }

  public function testValidateReturnsFalseInvalidToken() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');

    $tokens->shouldReceive('find')->once()->andReturnNull();

    $driver = new \Tappleby\AuthToken\AuthTokenDriver($tokens, $users);

    $this->assertFalse( $driver->validate('bad_token') );
  }

  public function testFilterReturnsFalseValidTokenMissingUser() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');

    $tokens->shouldReceive('find')->once()->andReturn( new \Tappleby\AuthToken\AuthToken(1, 'public', 'private') );
    $users->shouldReceive('retrieveByID')->once()->andReturnNull();

    $driver = new \Tappleby\AuthToken\AuthTokenDriver($tokens, $users);

    $this->assertFalse( $driver->validate('good_token') );
  }

  public function testValidateReturnsUsers() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');

    $tokens->shouldReceive('find')->once()->andReturn( new \Tappleby\AuthToken\AuthToken(1, 'public', 'private') );

    $user = m::mock('StdClass');
    $users->shouldReceive('retrieveByID')->once()->andReturn( $user );


    $driver = new \Tappleby\AuthToken\AuthTokenDriver($tokens, $users);
    $u = $driver->validate('good_token');

    $this->assertEquals($user, $u);
  }

  public function testUserFromAuthToken() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');
    $authToken = m::mock('Tappleby\AuthToken\AuthToken');

    $user = m::mock('StdClass');
    $users->shouldReceive('retrieveByID')->once()->andReturn( $user );
    $authToken->shouldReceive('getAuthIdentifier')->once()->andReturn(1);

    $driver = new \Tappleby\AuthToken\AuthTokenDriver($tokens, $users);
    $u = $driver->user( $authToken );

    $this->assertEquals($user, $u);
  }

  public function testAttemptReturnsFalseOnNullUser() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');

    $users->shouldReceive('retrieveByCredentials')->once()->andReturnNull();

    $driver = new \Tappleby\AuthToken\AuthTokenDriver($tokens, $users);

    $this->assertFalse( $driver->attempt( array() ) );
  }

  public function testAttempReturnsFalseOnFailedCredentials() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');
    $user = m::mock('Illuminate\Auth\UserInterface');

    $users->shouldReceive('retrieveByCredentials')->once()->andReturn($user);
    $users->shouldReceive('validateCredentials')->once()->andReturn(false);

    $driver = new \Tappleby\AuthToken\AuthTokenDriver($tokens, $users);

    $this->assertFalse( $driver->attempt( array() ) );
  }

  public function  testAttemptPurgesAndReturnsAuthToken() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');
    $user = m::mock('Illuminate\Auth\UserInterface');

    $authToken = m::mock('Tappleby\AuthToken\AuthToken');

    $users->shouldReceive('retrieveByCredentials')->once()->andReturn($user);
    $users->shouldReceive('validateCredentials')->once()->andReturn(true);

    $tokens->shouldReceive('purge')->once();
    $tokens->shouldReceive('create')->once()->andReturn($authToken);

    $driver = new \Tappleby\AuthToken\AuthTokenDriver($tokens, $users);

    $token = $driver->attempt(array());

    $this->assertEquals($authToken, $token);
  }
}