<?php

namespace Cookbook\Core\Bus;

use Illuminate\Support\ServiceProvider;

class BusServiceProvider extends ServiceProvider
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
        $this->app->singleton('Cookbook\Core\Bus\CommandDispatcher', function ($app) {
            return new CommandDispatcher($app, function () use ($app) {
                return $app['Illuminate\Contracts\Queue\Queue'];
            });
        });

        $this->app->singleton('Cookbook\Core\Bus\ApiCommandDispatcher', function ($app) {
            return new ApiCommandDispatcher($app, function () use ($app) {
                return $app['Cookbook\Core\Bus\CommandDispatcher'];
            });
        });

        $this->app->alias(
            'Cookbook\Core\Bus\CommandDispatcher', 'Illuminate\Contracts\Bus\Dispatcher'
        );

        $this->app->alias(
            'Cookbook\Core\Bus\CommandDispatcher', 'Illuminate\Contracts\Bus\QueueingDispatcher'
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
            'Cookbook\Core\Bus\CommandDispatcher',
            'Illuminate\Contracts\Bus\Dispatcher',
            'Illuminate\Contracts\Bus\QueueingDispatcher',
        ];
    }
}
