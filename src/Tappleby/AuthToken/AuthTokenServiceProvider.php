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
      $driver = $app['tappleby.auth.token']->driver();
      $events = $app['events'];

      return new AuthTokenFilter($driver, $events);
    });

    $this->app['tappleby.auth.token.controller'] = $this->app->share(function($app) {
      $driver = $app['tappleby.auth.token']->driver();

      return new AuthTokenController($driver);
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