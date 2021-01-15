<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool\Queue;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Support\Arr;

class TarantoolConnector implements ConnectorInterface
{
    /**
     * Database connections.
     * @var ConnectionResolverInterface
     */
    protected $connections;

    /**
     * Create a new connector instance.
     * @param ConnectionResolverInterface $connections
     */
    public function __construct(ConnectionResolverInterface $connections)
    {
        $this->connections = $connections;
    }

    /**
     * @inheritDoc
     */
    public function connect(array $config)
    {
        return new TarantoolQueue(
            $this->connections->connection(Arr::get($config, 'connection')),
            $config['table'],
            $config['queue'],
            Arr::get($config, 'expire', 60)
        );
    }
}
