<?php

namespace Chocofamily\Tarantool\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Chocofamily\Tarantool\Connection;

class Builder extends BaseBuilder
{
    /**
     * @inheritdoc
     */
    public function __construct(Connection $connection, Processor $processor = null, Grammar $grammar = null)
    {
        $this->grammar = $grammar ?: new Grammar;
        $this->connection = $connection;
        $this->processor = $processor;
    }

    /**
     * @inheritdoc
     */
    public function exists()
    {
        return !is_null($this->first());
    }

    /**
     * @inheritdoc
     */
    public function newQuery()
    {
        return new Builder($this->connection, $this->processor);
    }
}
