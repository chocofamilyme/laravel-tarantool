<?php

namespace Chocofamily\Tarantool;

use Tarantool\Client\Client as TarantoolClient;
use Chocofamily\Tarantool\Traits\Dsn;
use Chocofamily\Tarantool\Traits\Query;
use Chocofamily\Tarantool\Traits\Helper;
use Chocofamily\Tarantool\Query\Grammar as QGrammar;
use Chocofamily\Tarantool\Query\Processor as QProcessor;
use Chocofamily\Tarantool\Schema\Grammar as SGrammar;

//use Illuminate\Database\Query\Builder as Builder;
use Chocofamily\Tarantool\Query\Builder as Builder;

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
        $this->config = $config;
        $dsn = $this->getDsn($config);

        $connection = $this->createConnection($dsn);

        $this->setClient($connection);

        $this->useDefaultPostProcessor();
        $this->useDefaultSchemaGrammar();
        $this->useDefaultQueryGrammar();
    }

    /**
     * Create a new Tarantool connection.
     *
     * @param  string $dsn
     * @return \Tarantool\Client\Client
     */
    protected function createConnection(string $dsn)
    {
        return TarantoolClient::fromDsn($dsn);
    }

    /**
     * Begin a fluent query against a database table.
     *
     * @param  string $table
     * @param null $as
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($table, $as = null)
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
        return new Builder($this, $this->getDefaultQueryGrammar(), $this->getDefaultPostProcessor());
    }

    /**
     * @inheritdoc
     */
    public function getSchemaBuilder()
    {
        return new SchemaBuilder($this);
    }

    /**
     * @param $connection
     *
     * @return self
     */
    public function setClient(TarantoolClient $connection): self
    {
        $this->connection = $connection;

        return $this;
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
     * @inheritdoc
     */
    public function getDriverName()
    {
        return 'tarantool';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultPostProcessor()
    {
        return new QProcessor();
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultQueryGrammar()
    {
        return new QGrammar();
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultSchemaGrammar()
    {
        return new SGrammar();
    }
}
