<?php namespace Tappleby\AuthToken;

use Illuminate\Support\ServiceProvider;

class AuthTokenServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	public function boot()
	{
		$this->package('tappleby/laravel-auth-token');
		$this->app['router']->filter('auth.token', 'tappleby.auth.token.filter');
	}


	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$app = $this->app;

		$app->bindShared('tappleby.auth.token', function ($app) {
			return new AuthTokenManager($app);
		});

		$app->bindShared('tappleby.auth.token.filter', function ($app) {
			$driver = $app['tappleby.auth.token']->driver();
      $events = $app['events'];

      return new AuthTokenFilter($driver, $events);
		});

		$app->bind('Tappleby\AuthToken\AuthTokenController', function ($app) {
			$driver = $app['tappleby.auth.token']->driver();
			$credsFormatter = $app['config']->get('laravel-auth-token::format_credentials', null);
			$events = $app['events'];

      return new AuthTokenController($driver, $credsFormatter, $events);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('tappleby.auth.token', 'tappleby.auth.token.filter');
	}

}