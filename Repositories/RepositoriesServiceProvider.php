<?php

namespace Cookbook\Core\Repositories;

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
		$this->app->singleton('Cookbook\Core\Repositories\ObjectResolver', function ($app) {
			return new ObjectResolver($app['Illuminate\Contracts\Container\Container']);
		});

		$this->app->alias(
			'Cookbook\Core\Repositories\ObjectResolver', 'Cookbook\Contracts\Core\ObjectResolverContract'
		);

		$this->app->singleton('Cookbook\Core\Repositories\TrunkCache', function ($app) {
			return new TrunkCache();
		});

		$this->app->alias(
			'Cookbook\Core\Repositories\TrunkCache', 'Cookbook\Contracts\Core\TrunkContract'
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
			'Cookbook\Core\Repositories\ObjectResolver',
			'Cookbook\Contracts\Core\ObjectResolverContract',
			'Cookbook\Core\Repositories\TrunkCache',
			'Cookbook\Contracts\Core\TrunkContract',
		];
	}
}
