<?php

namespace Cookbook\Core\Event;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('events', function ($app) {
			return (new EventDispatcher($app))->setQueueResolver(function () use ($app) {
				return $app->make('Illuminate\Contracts\Queue\Factory');
			});
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}
}
