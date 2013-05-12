<?php
/*
 * User: tappleby
 * Date: 2013-05-11
 * Time: 9:33 PM
 */

namespace Tappleby\Support\Facades;

use Illuminate\Support\Facades\Facade;

class AuthToken extends Facade {

  protected static function getFacadeAccessor() { return 'tappleby.auth.token'; }
}