<?php

namespace Congraph\Core\Bus;

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
        var_dump("REGISTER");
        $this->app->singleton('Congraph\Core\Bus\CommandDispatcher', function ($app) {
            return new CommandDispatcher($app, function () use ($app) {
                return $app['Illuminate\Contracts\Queue\Queue'];
            });
        });

        $this->app->bind(
            'Illuminate\Contracts\Bus\Dispatcher',
            'Congraph\Core\Bus\CommandDispatcher'
        );

        $this->app->bind(
            'Illuminate\Contracts\Bus\QueueingDispatcher',
            'Congraph\Core\Bus\CommandDispatcher'
        );

        // $this->app->alias(
        //     'Congraph\Core\Bus\CommandDispatcher', 'Illuminate\Contracts\Bus\Dispatcher'
        // );

        // $this->app->alias(
        //     'Congraph\Core\Bus\CommandDispatcher', 'Illuminate\Contracts\Bus\QueueingDispatcher'
        // );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'Congraph\Core\Bus\CommandDispatcher',
            'Illuminate\Contracts\Bus\Dispatcher',
            'Illuminate\Contracts\Bus\QueueingDispatcher',
        ];
    }
}
