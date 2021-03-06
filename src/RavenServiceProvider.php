<?php

namespace Clowdy\Raven;

use Illuminate\Support\ServiceProvider;
use Monolog\Handler\RavenHandler;

class RavenServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('raven.php'),
        ], 'config');

        if (! config('raven.enabled')) {
            return;
        }

        $this->app['log']->registerHandler(
            config('raven.level', 'error'),
            function ($level) {
                $handler = new RavenHandler($this->app[Client::class], $level);

                // Add processors
                $processors = config('raven.monolog.processors', []);

                if (is_array($processors)) {
                    foreach ($processors as $process) {
                        // Get callable
                        if (is_callable($process)) {
                            $callable = $process;
                        } elseif (is_string($process)) {
                            $callable = new $process();
                        } else {
                            throw new \Exception('Raven: Invalid processor');
                        }

                        // Add processor to Raven handler
                        $handler->pushProcessor($callable);
                    }
                }

                return $handler;
            }
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'raven');

        $this->app[Client::class] = $this->app->share(function ($app) {
            return new Client(config('raven'), $app['queue'], $app->environment());
        });

        $this->app->instance('log', new Log($this->app['log']->getMonolog(), $this->app['log']->getEventDispatcher()));
    }
}
