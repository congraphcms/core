<?php

namespace Congraph\Core\Bus;

use Illuminate\Support\ServiceProvider;

class BusServiceProvider extends ServiceProvider
{


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Congraph\Core\Bus\CommandDispatcher', function ($app) {
            return new CommandDispatcher($app, function () use ($app) {
                return $app['Illuminate\Contracts\Queue\Queue'];
            });
        });

        $this->app->alias(
            'Congraph\Core\Bus\CommandDispatcher', 'Illuminate\Contracts\Bus\Dispatcher'
        );

        $this->app->alias(
            'Congraph\Core\Bus\CommandDispatcher', 'Illuminate\Contracts\Bus\QueueingDispatcher'
        );
    }
}
