<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 11:42 PM
 */

use Mockery as m;

class AuthTokenFilterTest extends PHPUnit_Framework_TestCase {

  public function tearDown()
  {
    m::close();
  }

  public function setUp() {
    m::mock('Illuminate\Auth\UserInterface');
  }

  public function testFilterValidateFailsFiresNotAuthorizedException() {
    $driver = m::mock('Tappleby\AuthToken\AuthTokenDriver');
    $events = m::mock('Illuminate\Events\Dispatcher');
    $route = m::mock('Illuminate\Routing\Route');
    $request = m::mock('Illuminate\Http\Request');


    $request->shouldReceive('header')->once()->andReturnNull();
	  $request->shouldReceive('input')->once()->andReturnNull();

    $driver->shouldReceive('validate')->andReturn(false);

    $this->setExpectedException('Tappleby\AuthToken\Exceptions\NotAuthorizedException');

    $filter = new \Tappleby\AuthToken\AuthTokenFilter($driver, $events);
    $filter->filter($route, $request);

  }

  public function testFilterValidEventFired() {
    $driver = m::mock('Tappleby\AuthToken\AuthTokenDriver');
    $events = m::mock('Illuminate\Events\Dispatcher');
    $route = m::mock('Illuminate\Routing\Route');
    $request = m::mock('Illuminate\Http\Request');


    $request->shouldReceive('header')->once()->andReturnNull();
	  $request->shouldReceive('input')->once()->andReturnNull();

    $user = m::mock('StdClass');
    $driver->shouldReceive('validate')->andReturn($user);

    $events->shouldReceive('fire')->once()->with('auth.token.valid', $user);

    $filter = new \Tappleby\AuthToken\AuthTokenFilter($driver, $events);
    $filter->filter($route, $request);
  }



  /*public function testFilterExceptionMissingToken() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');
    $events = m::mock('Illuminate\Events\Dispatcher');
    $route = m::mock('Illuminate\Routing\Route');
    $request = m::mock('Illuminate\Http\Request');




    $this->setExpectedException('Tappleby\AuthToken\Exceptions\NotAuthorizedException');

    $filter = new \Tappleby\AuthToken\AuthTokenFilter($tokens, $users, $events);
    $filter->filter($route, $request);

  }

  public function testFilterInvalidToken() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');
    $events = m::mock('Illuminate\Events\Dispatcher');
    $route = m::mock('Illuminate\Routing\Route');
    $request = m::mock('Illuminate\Http\Request');


    $request->shouldReceive('header')->once()->andReturn('BAD');
    $tokens->shouldReceive('find')->once()->andReturnNull();

    $this->setExpectedException('Tappleby\AuthToken\Exceptions\NotAuthorizedException');

    $filter = new \Tappleby\AuthToken\AuthTokenFilter($tokens, $users, $events);
    $filter->filter($route, $request);
  }

  public function testFilterExceptionValidTokenMissingUser() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');
    $events = m::mock('Illuminate\Events\Dispatcher');
    $route = m::mock('Illuminate\Routing\Route');
    $request = m::mock('Illuminate\Http\Request');


    $request->shouldReceive('header')->once()->andReturn('token');
    $tokens->shouldReceive('find')->once()->andReturn( new \Tappleby\AuthToken\AuthToken(1, 'public', 'private') );
    $users->shouldReceive('retrieveByID')->once()->andReturnNull();

    $this->setExpectedException('Tappleby\AuthToken\Exceptions\NotAuthorizedException');

    $filter = new \Tappleby\AuthToken\AuthTokenFilter($tokens, $users, $events);
    $filter->filter($route, $request);
  }

  public function testFilterValidEventFired() {
    $tokens = m::mock('Tappleby\AuthToken\AuthTokenProviderInterface');
    $users = m::mock('Illuminate\Auth\UserProviderInterface');
    $events = m::mock('Illuminate\Events\Dispatcher');
    $route = m::mock('Illuminate\Routing\Route');
    $request = m::mock('Illuminate\Http\Request');


    $request->shouldReceive('header')->once()->andReturn('token');
    $tokens->shouldReceive('find')->once()->andReturn( new \Tappleby\AuthToken\AuthToken(1, 'public', 'private') );

    $user = m::mock('StdClass');
    $users->shouldReceive('retrieveByID')->once()->andReturn( $user );

    $events->shouldReceive('fire')->once()->with('auth.token.valid', $user);

    $filter = new \Tappleby\AuthToken\AuthTokenFilter($tokens, $users, $events);
    $filter->filter($route, $request);
  }  */

}