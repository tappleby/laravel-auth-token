<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 11:26 PM
 */

namespace Tappleby\AuthToken;


use Illuminate\Routing\Controllers\Controller;

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

  }
}