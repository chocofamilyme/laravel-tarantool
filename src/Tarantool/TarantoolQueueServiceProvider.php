<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool;

use Chocofamily\Tarantool\Queue\TarantoolFailedJobProvider;
use Illuminate\Support\ServiceProvider;

class TarantoolQueueServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->app->singleton('queue.failer', function ($app) {
            return new TarantoolFailedJobProvider(
                $app['db'],
                $app['config']['queue.failed.database'],
                $app['config']['queue.failed.table']
            );
        });
    }
}
