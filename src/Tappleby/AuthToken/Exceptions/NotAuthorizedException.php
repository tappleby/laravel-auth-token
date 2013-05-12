<?php
/*
 * User: tappleby
 * Date: 2013-05-12
 * Time: 12:17 AM
 */

namespace Tappleby\AuthToken\Exceptions;


class NotAuthorizedException extends \Exception {
  public function __construct($message = "Not Authorized", $code = 401, Exception $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}