<?php

namespace Chocofamily\Tarantool;

use Illuminate\Database\Connection as BaseConnection;
use Tarantool\Client\Client;

class Connection extends BaseConnection
{
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

        // Build the connection string
        $dsn = $this->getDsn($config);

        // Create the connection
        $this->connection = $this->createConnection($dsn);

        $this->useDefaultPostProcessor();
        $this->useDefaultSchemaGrammar();
        $this->useDefaultQueryGrammar();
    }

    /**
     * Begin a fluent query against a database collection.
     *
     * @param  string $space
     * @return Query\Builder
     */
    public function spaceBuilder($space)
    {
        $query = new Query\Builder($this, $this->getPostProcessor());

        return $query->from($space);
    }

    /**
     * Begin a fluent query against a database space.
     *
     * @param  string $table
     * @return Query\Builder
     */
    public function table($table)
    {
        return $this->spaceBuilder($table);
    }

    public function getSpace($spaceName)
    {
        $this->getClient()->getSpace($spaceName);
    }

    /**
     * @inheritdoc
     */
    public function getSchemaBuilder()
    {
        return new Schema\Builder($this);
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
     * Determine if the given configuration array has a dsn string.
     *
     * @param  array  $config
     * @return bool
     */
    protected function hasDsnString(array $config)
    {
        return isset($config['dsn']) && ! empty($config['dsn']);
    }

    /**
     * Get the DSN string form configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDsnString(array $config)
    {
        return $config['dsn'];
    }

    /**
     * Get the DSN string for a host / port configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getHostDsn(array $config)
    {
        $host = $config['host'];

        if (strpos($host, ':') === false && !empty($config['port'])) {
            $host = $host . ':' . $config['port'];
        }

        $auth = $config['username'] . ':' . $config['password'];

        $options = isset($config['options']) && !empty($config['options']) ? http_build_query($config['options'], null, '&') : null;

        $optionConnectType = isset($config['type']) && !empty($config['type']);
        $connType = (($optionConnectType || ($optionConnectType && in_array($config['type'], ['tcp', 'unix']))) ? $config['type'] : 'tcp');

        return $connType . '://' . $auth . '@' . $host . ($options ? '/?' . $options : '');
    }

    /**
     * Create a DSN string from a configuration.
     *
     * @param  array $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        return $this->hasDsnString($config)
            ? $this->getDsnString($config)
            : $this->getHostDsn($config);
    }

    /**
     * @inheritdoc
     */
    public function getElapsedTime($start)
    {
        return parent::getElapsedTime($start);
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
        return new Query\Processor();
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultQueryGrammar()
    {
        return new Query\Grammar();
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultSchemaGrammar()
    {
        return new Schema\Grammar();
    }
}
