<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool;

use Chocofamily\Tarantool\Query\Builder;
use Chocofamily\Tarantool\Query\Grammar as QGrammar;
use Chocofamily\Tarantool\Query\Processor as QProcessor;
use Chocofamily\Tarantool\Schema\Grammar as SGrammar;
use Chocofamily\Tarantool\Traits\Dsn;
use Chocofamily\Tarantool\Traits\Helper;
use Chocofamily\Tarantool\Traits\Query;
use Illuminate\Database\Connection as BaseConnection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Tarantool\Client\Client as TarantoolClient;
use Tarantool\Client\SqlQueryResult;

class Connection extends BaseConnection
{
    use Dsn, Query, Helper;

    /** @var TarantoolClient */
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
     * @return TarantoolClient
     */
    protected function createConnection(string $dsn)
    {
        return TarantoolClient::fromDsn($dsn);
    }

    /**
     * Begin a fluent query against a database table.
     *
     * @param \Closure|\Illuminate\Database\Query\Builder|string $table
     * @param string|null $as
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($table, $as = null)
    {
        return $this->query()->from($table);
    }

    /**
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return \Generator
     * @throws \Exception
     */
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        /** @var SqlQueryResult $queryResult */
        $queryResult = $this->run($query, $bindings, function () {
        });

        $metaData = $queryResult->getMetadata();

        array_walk_recursive($metaData, function (&$value) {
            $value = strtolower($value);
        });

        $result = new SqlQueryResult($queryResult->getData(), $metaData);

        return $result->getIterator();
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
     * {@inheritdoc}
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
     * @return TarantoolClient
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
     * {@inheritdoc}
     */
    public function getDriverName()
    {
        return 'tarantool';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultPostProcessor()
    {
        return new QProcessor();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultQueryGrammar()
    {
        return new QGrammar();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultSchemaGrammar()
    {
        return new SGrammar();
    }
}
