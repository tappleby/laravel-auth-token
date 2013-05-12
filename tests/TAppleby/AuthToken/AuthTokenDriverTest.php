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

//    $this->setExpectedException('Tappleby\AuthToken\Exceptions\NotAuthorizedException');

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

}