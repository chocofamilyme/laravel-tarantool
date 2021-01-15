<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool\Queue;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Queue\DatabaseQueue;
use Chocofamily\Tarantool\Connection;
use Illuminate\Queue\Jobs\DatabaseJobRecord;

class TarantoolQueue extends DatabaseQueue
{
    /**
     * The expiration time of a job.
     * @var int|null
     */
    protected $retryAfter = 60;

    /**
     * The connection name for the queue.
     * @var string
     */
    protected $connectionName;

    /**
     * @inheritdoc
     */
    public function __construct(Connection $database, $table, $default = 'default', $retryAfter = 60)
    {
        parent::__construct($database, $table, $default, $retryAfter);
        $this->retryAfter = $retryAfter;
    }

    /**
     * @inheritdoc
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        if ($job = $this->getNextAvailableJob($queue)) {
            return $this->marshalJob($queue, $job);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function getNextAvailableJob($queue)
    {
        $builder = $this->database->table($this->table)
            ->where('queue', $this->getQueue($queue))
            ->where(function ($query) {
                $this->isAvailable($query);
                $this->isReservedButExpired($query);
            })
            ->orderBy('id')
            ->take(1);

        $job = $this->database->getClient()->call('box.func.get_active_job:call', [$this->getRealSql($builder), Carbon::now()->timestamp]);
        $job = array_pop($job);

        return $job ? new DatabaseJobRecord((object) $job) : null;
    }

    /**
     * @inheritdoc
     */
    protected function marshalJob($queue, $job)
    {
        return new TarantoolJob(
            $this->container, $this, $job, $this->connectionName, $queue
        );
    }

    /**
     * @inheritdoc
     */
    public function deleteReserved($queue, $id)
    {
        $this->database->table($this->table)->where('id', $id)->delete();
    }

    /**
     * @inheritdoc
     */
    public function deleteAndRelease($queue, $job, $delay)
    {
        $this->database->table($this->table)->where('id', $job->getJobId())->delete();
        $this->release($queue, $job->getJobRecord(), $delay);
    }

    /**
     * Get full sql query
     *
     * @param Builder $builder
     * @return string
     */
    private function getRealSql(Builder $builder)
    {
        $sql = $builder->toSql();
        foreach ($builder->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'".$binding."'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }

        return $sql;
    }
}