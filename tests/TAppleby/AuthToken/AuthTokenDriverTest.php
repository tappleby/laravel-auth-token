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

  public function testValidateExceptionNullToken() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');


    $this->setExpectedException('Tappleby\AuthToken\Exceptions\NotAuthorizedException');

    $driver = new \Tappleby\AuthToken\AuthTokenDriver($tokens, $users);
    $driver->validate(null);

  }

  public function testValidateExceptionInvalidToken() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');

    $tokens->shouldReceive('find')->once()->andReturnNull();

    $this->setExpectedException('Tappleby\AuthToken\Exceptions\NotAuthorizedException');

    $driver = new \Tappleby\AuthToken\AuthTokenDriver($tokens, $users);
    $driver->validate('bad_token');
  }

  public function testFilterExceptionValidTokenMissingUser() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');


    $tokens->shouldReceive('find')->once()->andReturn( new \Tappleby\AuthToken\AuthToken(1, 'public', 'private') );
    $users->shouldReceive('retrieveByID')->once()->andReturnNull();

    $this->setExpectedException('Tappleby\AuthToken\Exceptions\NotAuthorizedException');

    $driver = new \Tappleby\AuthToken\AuthTokenDriver($tokens, $users);
    $driver->validate('good_token');
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