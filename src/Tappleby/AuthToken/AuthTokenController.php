<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 11:26 PM
 */

namespace Tappleby\AuthToken;

use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Controller;
use Tappleby\AuthToken\Exceptions\NotAuthorizedException;
use Response;
use Request;
use Input;
use Validator;

class AuthTokenController extends Controller {

  /**
   * @var \Tappleby\AuthToken\AuthTokenDriver
   */
  protected $driver;

	/**
	 * @var callable format username and password into hash for Auth::attempt
	 */
	protected $credentialsFormatter;

	/**
	 * @var \Illuminate\Events\Dispatcher
	 */
	protected $events;

	function __construct(AuthTokenDriver $driver, \Closure $credentialsFormatter, Dispatcher $events)
  {
    $this->driver = $driver;
	  $this->credentialsFormatter = $credentialsFormatter;
	  $this->events = $events;
  }

  protected function getAuthToken() {

	  $token = Request::header('X-Auth-Token');

	  if(empty($token)) {
		  $token = Input::get('auth_token');
	  }

	  return $token;
  }

  public function index() {

    $payload = $this->getAuthToken();
    $user = $this->driver->validate($payload);

    if(!$user) {
      throw new NotAuthorizedException();
    }

    return Response::json($user);
  }

  public function store() {

    $input = Input::all();

    $validator = Validator::make(
      $input,
      array('username' => array('required'), 'password' => array('required'))
    );

    if($validator->fails()) {
      throw new NotAuthorizedException();
    }

	  $creds = call_user_func($this->credentialsFormatter, $input['username'], $input['password']);
    $token = $this->driver->attempt($creds);

    if(!$token) {
      throw new NotAuthorizedException();
    }

	  $user = $this->driver->user($token);

	  $this->events->fire('auth.token.created', array($user, $token));
	  $serializedToken = $this->driver->getProvider()->serializeToken($token);


	  return Response::json(array('token' => $serializedToken, 'user' => $user->toArray()));
  }

  public function destroy() {
    $payload = $this->getAuthToken();
    $user = $this->driver->validate($payload);

    if(!$user) {
      throw new NotAuthorizedException();
    }

    $this->driver->getProvider()->purge($user);
	  $this->events->fire('auth.token.deleted', array($user));

    return Response::json(array('success' => true));
  }
}