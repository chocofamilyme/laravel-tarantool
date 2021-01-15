<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool;

use Chocofamily\Tarantool\Queue\TarantoolFailedJobProvider;
use Illuminate\Queue\QueueServiceProvider;

class TarantoolQueueServiceProvider extends QueueServiceProvider
{
    /**
     * @inheritDoc
     */
    protected function databaseFailedJobProvider($config)
    {
        return new TarantoolFailedJobProvider($this->app['db'], $config['database'], $config['table']);
    }
}
