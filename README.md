# Laravel 4 Auth token

Hooks into the laravel auth module and provides an auth token upon success. This token is really only secure in https environment. This main purpose for this module was to provide an auth token to javascript web app which could be used to identify users on api calls.

[![Build Status](https://travis-ci.org/tappleby/laravel-auth-token.png?branch=master)](https://travis-ci.org/tappleby/laravel-auth-token)

Upgrading to Laravel 4.1?, see the [breaking changes](#changes) 

## Getting Started

### Setup

Add the package to your `composer.json`

    "require": {
		...
        "tappleby/laravel-auth-token": "0.3.*"
    }

Add the service provider to `app/config/app.php`

	'Tappleby\AuthToken\AuthTokenServiceProvider',
	
Setup the optional aliases in `app/config/app.php`

	'AuthToken' => 'Tappleby\Support\Facades\AuthToken',
    'AuthTokenNotAuthorizedException' => 'Tappleby\AuthToken\Exceptions\NotAuthorizedException'
    
Currently the auth tokens are stored in the database, you will need to run the migrations:

	php artisan migrate --package=tappleby/laravel-auth-token
	
##### Optional configuration

This package defaults to using email as the username field to validate against, this can be changed via the package configuration.

1. Publish the configuration `php artisan config:publish tappleby/laravel-auth-token`
2. Edit the `format_credentials` closure in `app/config/packages/tappleby/laravel-auth-token/config.php`

Example - Only validate active users and check the username column instead of email:

	'format_credentials' => function ($username, $password) {
		return array(
			'username' => $username,
			'password' => $password,
			'active' => true
		);
	}

You can read more about the laravel Auth module here: [Authenticating Users](http://laravel.com/docs/security#authenticating-users)

### The controller

A default controller is provided to grant, check and revoke tokens. Add the following to `app/routes.php`

	Route::get('auth', 'Tappleby\AuthToken\AuthTokenController@index');
	Route::post('auth', 'Tappleby\AuthToken\AuthTokenController@store');
	Route::delete('auth', 'Tappleby\AuthToken\AuthTokenController@destroy');
	

### CORS Support

CORS support is not built into this library by default, it can be enabled by using the following package: [barryvdh/laravel-cors](https://github.com/barryvdh/laravel-cors).

The configuration will be specific to how your routing is setup. If you are using the `X-Auth-Token` header, it is important to add this to the `allowedHeaders` configuration. See the package documentation for further configuration details. 

Heres an example using the default `auth` route:

    'paths' => array(
        'auth' => array(
            'allowedOrigins' => array('*'),
            'allowedHeaders' => array('Content-Type', 'X-Auth-Token'),
            'allowedMethods' => array('POST', 'PUT', 'GET', 'DELETE'),
            'maxAge' => 3600,
        )
    ),

> Note: If you know the list of `allowedOrigins` it might be best to define them explicitly instead of using the wildcard `*`

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
	
### Events

The route filter will trigger `auth.token.valid` with the authorized user when a valid auth token is provided. 

	Event::listen('auth.token.valid', function($user)
	{
	  //Token is valid, set the user on auth system.
	  Auth::setUser($user);
	});

AuthTokenController::store will trigger `auth.token.created` before returning the response.

	Event::listen('auth.token.created', function($user, $token)
	{
		$user->load('relation1', 'relation2');
	});

AuthTokenController::destroy will trigger `auth.token.deleted` before returning the response.

### Handling the NotAuthorizedException

Optionally register the `NotAuthorizedException` as alias eg. `AuthTokenNotAuthorizedException`

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

## Changes

*0.3.0*

- Added `auth.token.created` event which gets triggered before response is returned in AuthTokenController::store
- AuthTokenController requires the event dispatcher to be passed to constructor.

*0.2.0*

- Adds support for Laravel 4.1.X. This is a hard dependency due to API changes in L4.1
- Removed the facade for AuthTokenController, must use the full namespace to controller. see [The controller section](#the-controller)
- Optional configuration for Auth::attempt fields.


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
