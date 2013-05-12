<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 11:26 PM
 */

namespace Tappleby\AuthToken;

use Illuminate\Routing\Controllers\Controller;
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

  public function index() {

    $payload = \Request::header('X-Auth-Token');
    $user = $this->driver->validate($payload);

    if(!$user) {
      throw new NotAuthorizedException();
    }

    return \Response::json($user);
  }

  public function save() {

    $input = \Input::all();

    $validator = \Validator::make(
      $input,
      array('email' => array('required'), 'password' => array('required'))
    );

    if($validator->fails()) {
      throw new NotAuthorizedException();
    }

    $token = $this->driver->attempt(array('email' => $input['email'], 'password' => $input['password']));

    if(!$token) {
      throw new NotAuthorizedException();
    }

    $serializedToken = $this->driver->getProvider()->serializeToken($token);

    return \Response::json($serializedToken);
  }
}