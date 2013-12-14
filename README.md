# Laravel 4 Auth token

Hoooks into the laravel auth module and provides an auth token upon success. This token is really only secure in https environment. This main purpose for this module was to provide an auth token to javascript web app which could be used to identify users on api calls.

[![Build Status](https://travis-ci.org/tappleby/laravel-auth-token.png?branch=master)](https://travis-ci.org/tappleby/laravel-auth-token)

## Getting Started

### Setup

Add the pacakge to your `composer.json`

    "require": {
		...
        "tappleby/laravel-auth-token": "0.1.X"
    }

Add the service provider to `app/config/app.php`

	'Tappleby\AuthToken\AuthTokenServiceProvider',
	
Setup the optional aliases in `app/config/app.php`

	'AuthToken' => 'Tappleby\Support\Facades\AuthToken',
    'AuthTokenController' => 'Tappleby\Support\Facades\AuthTokenController',
    'AuthTokenNotAuthorizedException' => 'Tappleby\AuthToken\Exceptions\NotAuthorizedException'

### The controller

A default controller is provided to grant, check and revoke tokens. Add the following to `app/routes.php`

	Route::get('auth', 'AuthTokenController@index');
	Route::post('auth', 'AuthTokenController@store');
	Route::delete('auth', 'AuthTokenController@destroy');

##### Request parameters

All request must include one of:

1. `X-Auth-Token` header.
2. `auth_token` field.

##### `GET` Index action

Returns current user as json. Requires auth token parameter to be present. On Fail throws `NotAuthorizedException`.   

##### `POST` Store action

Required input `username` and `password`. On success returns json object containing `token` and `user`. On Fail throws `NotAuthorizedException`.

##### `DELETE` Destroy action

Purges the users tokens. Requires auth token parameter to be present. On Fail throws `NotAuthorizedException`.

`NotAuthorizedException` has a `401` error code by default.
    
### Route Filter

An `auth.token` route filter gets registered by the service provider. To protect a resource just register a before filter. Filter will throw an `NotAuthorizedException` if a valid auth token is invalid or missing.

	Route::group(array('prefix' => 'api', 'before' => 'auth.token'), function() {
	  Route::get('/', function() {
	    return "Protected resource";
	  });
	});	 
	
### Token valid event

The route filter will trigger `auth.token.valid` with the authorized user when a valid auth token is provided. 

	Event::listen('auth.token.valid', function($user)
	{
	  //Token is valid, set the user on auth system.
	  Auth::setUser($user);
	}); 
    
### Handling the NotAuthorizedException

Optionalliy register the `NotAuthorizedException` as alias eg. `AuthTokenNotAuthorizedException`

	App::error(function(AuthTokenNotAuthorizedException $exception) {
	  if(Request::ajax()) {
	    return Response::json(array('error' => $exception->getMessage()), $exception->getCode());
	  }
	  
	  …Handle non ajax response…
	});

## Combining Laravel Auth with AuthToken

Some apps might already be using the traditional laravel based auth. The following can be used to manually generate a token.

	if(Auth::check()) {
	  $authToken = AuthToken::create(Auth::user());
      $publicToken = AuthToken::publicToken($authToken);
	}
	
The `AuthToken::publicToken` method prepares the auth token to be sent to the browser.

## Pro tip: Using with jQuery

Using the jQuery ajaxPrefilter method the X-Auth-Token can be set automatically on ajax request.

	// Register ajax prefilter. If app config contains auth_token will automatically set header,
	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
	  if (config.auth_token) {
	    jqXHR.setRequestHeader('X-Auth-Token', config.auth_token);
	  }
	});
	
If a 401 response code is recieved it can also handled automatically. In the following example I opted to redirect to logout page to ensure user session was destroyed.

	// If a 401 http error is recieved, automatically redirect to logout page.
	$(document).ajaxError(function (event, jqxhr) {
	  if (jqxhr && jqxhr.status === 401) {
	    window.location = '/logout';
	  }
	});
	
## Pro tip: Automatically binding token data to view.

View composer can be used to automatically bind data to views. This keeps logic all in one spot. I use the following to setup config variables for javascript.

    View::composer('layouts.default', function($view)
    {
      $rootUrl = rtrim(URL::route('home'), '/');
    
      $jsConfig = isset($view->jsConfig) ? $view->jsConfig : array();
    
      $jsConfig = array_merge(array(
        'rootUrl' =>  $rootUrl
      ), $jsConfig);
    
      if(Auth::check()) {
    
        $authToken = AuthToken::create(Auth::user());
        $publicToken = AuthToken::publicToken($authToken);
    
        $userData = array_merge(
          Auth::user()->toArray(),
          array('auth_token' => $publicToken)
        );
    
        $jsConfig['userData'] = $userData;
      }
    
      $view->with('jsConfig', $jsConfig);
    });