<?php namespace Tappleby\AuthToken;

use Illuminate\Support\ServiceProvider;

class AuthTokenServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
    $this->app['tappleby.auth.token'] = $this->app->share(function($app) {
      return new AuthTokenManager($app);
    });

    $this->app['tappleby.auth.token.filter'] = $this->app->share(function($app) {
      $tokens = $app['tappleby.auth.token']->driver()->getProvider();
      $users = $app['auth']->driver()->getProvider();
      $events = $app['events'];

      return new AuthTokenFilter($tokens, $users, $events);
    });

    $this->app['tappleby.auth.token.controller'] = $this->app->share(function($app) {
      $tokens = $app['tappleby.auth.token']->driver()->getProvider();
      $users = $app['auth']->driver()->getProvider();

      return new AuthTokenController($tokens, $users);
    });

    $this->app['router']->addFilter('auth.token', 'tappleby.auth.token.filter');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('tappleby.auth.token');
	}

}