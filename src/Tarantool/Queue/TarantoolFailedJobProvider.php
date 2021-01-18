<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool\Queue;

use Carbon\Carbon;
use Illuminate\Queue\Failed\DatabaseFailedJobProvider;

class TarantoolFailedJobProvider extends DatabaseFailedJobProvider
{
    /**
     * @inheritDoc
     */
    public function log($connection, $queue, $payload, $exception)
    {
        $failed_at = Carbon::now()->timestamp;
        $exception = (string)$exception;
        $this->getTable()->insert(compact('connection', 'queue', 'payload', 'failed_at', 'exception'));
    }


    /**
     * @inheritDoc
     */
    public function find($id)
    {
        return (object)parent::find($id);
    }
}
