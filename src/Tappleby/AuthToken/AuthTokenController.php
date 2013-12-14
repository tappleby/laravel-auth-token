<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 11:26 PM
 */

namespace Tappleby\AuthToken;

use Illuminate\Routing\Controller;
use Tappleby\AuthToken\Exceptions\NotAuthorizedException;

class AuthTokenController extends Controller {

  /**
   * @var \Tappleby\AuthToken\AuthTokenDriver
   */
  protected $driver;

  function __construct(AuthTokenDriver $driver)
  {
    $this->driver = $driver;
  }

  protected function getAuthToken() {

	  $token = \Request::header('X-Auth-Token');

	  if(empty($token)) {
		  $token = \Input::get('auth_token');
	  }

	  return $token;
  }

  public function index() {

    $payload = $this->getAuthToken();
    $user = $this->driver->validate($payload);

    if(!$user) {
      throw new NotAuthorizedException();
    }

    return \Response::json($user);
  }

  public function store() {

    $input = \Input::all();

    $validator = \Validator::make(
      $input,
      array('username' => array('required'), 'password' => array('required'))
    );

    if($validator->fails()) {
      throw new NotAuthorizedException();
    }

    $token = $this->driver->attempt(array('email' => $input['username'], 'password' => $input['password']));

    if(!$token) {
      throw new NotAuthorizedException();
    }

    $serializedToken = $this->driver->getProvider()->serializeToken($token);

    $user = $this->driver->user($token);

    return \Response::json(array('token' => $serializedToken, 'user' => $user->toArray()));
  }

  public function destroy() {
    $payload = $this->getAuthToken();
    $user = $this->driver->validate($payload);

    if(!$user) {
      throw new NotAuthorizedException();
    }

    $this->driver->getProvider()->purge($user);

    return \Response::json(array('success' => true));
  }
}