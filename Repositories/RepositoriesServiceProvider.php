<?php

namespace Congraph\Core\Repositories;

use Illuminate\Support\ServiceProvider;

class RepositoriesServiceProvider extends ServiceProvider
{

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('Congraph\Core\Repositories\ObjectResolver', function ($app) {
			return new ObjectResolver($app['Illuminate\Contracts\Container\Container']);
		});

		$this->app->alias(
			'Congraph\Core\Repositories\ObjectResolver', 'Congraph\Contracts\Core\ObjectResolverContract'
		);

		$this->app->singleton('Congraph\Core\Repositories\TrunkCache', function ($app) {
			return new TrunkCache();
		});

		$this->app->alias(
			'Congraph\Core\Repositories\TrunkCache', 'Congraph\Contracts\Core\TrunkContract'
		);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'Congraph\Core\Repositories\ObjectResolver',
			'Congraph\Contracts\Core\ObjectResolverContract',
			'Congraph\Core\Repositories\TrunkCache',
			'Congraph\Contracts\Core\TrunkContract',
		];
	}
}
