<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool;

use Chocofamily\Tarantool\Console\QueueTarantoolFunctionCommand;
use Chocofamily\Tarantool\Eloquent\Model;
use Chocofamily\Tarantool\Queue\TarantoolConnector;
use Chocofamily\Tarantool\Queue\TarantoolFailedJobProvider;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        Model::setConnectionResolver($this->app->get('db'));
        Model::setEventDispatcher($this->app->get('events'));

        $this->app->singleton('queue.failer', function ($app) {
            return new TarantoolFailedJobProvider(
                $app['db'],
                $app['config']['queue.failed.database'],
                $app['config']['queue.failed.table']
            );
        });
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->commands([
            QueueTarantoolFunctionCommand::class
        ]);

        // Add database driver.
        $this->app->resolving('db', function ($db) {
            $db->extend('tarantool', function ($config, $name) {
                $config['name'] = $name;

                return new Connection($config);
            });
        });

        // Add connector for queue support.
        $this->app->resolving('queue', function ($queue) {
            $queue->addConnector('tarantool', function () {
                return new TarantoolConnector($this->app['db']);
            });
        });

        $this->app->singleton('command.queue.tarantool-function', function ($app) {
            return new QueueTarantoolFunctionCommand($app['files'], $app['composer']);
        });
    }
}
