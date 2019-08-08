<?php

namespace Chocofamily\Tarantool;

use Tarantool\Client\Client;
use Chocofamily\Tarantool\Traits\Dsn;
use Chocofamily\Tarantool\Traits\Query;
use Chocofamily\Tarantool\Traits\Helper;
use Illuminate\Database\Query\Builder as Builder;
use Illuminate\Database\Connection as BaseConnection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class Connection extends BaseConnection
{
    use Dsn, Query, Helper;

    /**
     * The Tarantool connection handler.
     *
     * @var \Tarantool\Client\Client;
     */
    protected $connection;

    /**
     * Create a new database connection instance.
     *
     * @param  array $config
     */
    public function __construct(array $config)
    {
        $this->connection = $this->connect($config);
    }

    public function connect(array $config)
    {
        $this->config = $config;

        // Build the connection string
        $dsn = $this->getDsn($config);

        // Create the connection
        $connection = $this->createConnection($dsn);

        return $connection;
    }

    /**
     * Begin a fluent query against a database table.
     *
     * @param  string  $table
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($table)
    {
        return $this->query()->from($table);
    }
    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return new Builder($this, $this->getPostProcessor(), $this->getSchemaGrammar());
    }

    /**
     * @inheritdoc
     */
    public function getSchemaBuilder()
    {
        return new SchemaBuilder($this);
    }

    /**
     * return Tarantool object.
     *
     * @return \Tarantool\Client\Client
     */
    public function getClient()
    {
        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabaseName()
    {
        return $this->config['database'];
    }

    /**
     * Create a new Tarantool connection.
     *
     * @param  string $dsn
     * @return \Tarantool\Client\Client
     */
    protected function createConnection(string $dsn)
    {
        return Client::fromDsn($dsn);
    }

    /**
     * @inheritdoc
     */
    public function disconnect()
    {
        unset($this->connection);
    }

    /**
     * @inheritdoc
     */
    public function getDriverName()
    {
        return 'tarantool';
    }
}
