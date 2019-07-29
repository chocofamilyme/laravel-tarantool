<?php

namespace Chocofamily\Tarantool;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Jenssegers\Mongodb\Eloquent\Model;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot() {}
    /**
     * Register the service provider.
     */
    public function register()
    {
        // Add database driver.
        $this->app->resolving('db', function ($db) {
            $db->extend('tarantool', function ($config, $name) {
                $config['name'] = $name;
                return new Connection($config);
            });
        });
        /*
        // Add connector for queue support.
        $this->app->resolving('queue', function ($queue) {
            $queue->addConnector('tarantool', function () {
                return new TarantoolQueue($this->app['db']);
            });
        });
        */
    }
}