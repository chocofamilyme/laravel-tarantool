<?php

namespace Chocofamily\Tarantool\Traits;

trait Query
{
    /**
     * Run a select statement against the database.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return array
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->executeQuery($query, $bindings, $useReadPdo)->getData();
    }

    /**
     * Run an insert statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        $result = $this->executeQuery($query, $bindings);
        if (!empty($result)) {
            return false;
        }

        return true;
    }
    /**
     * Run an update statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function update($query, $bindings = [])
    {
        $result = $this->executeQuery($query, $bindings);

        if (!empty($result)) {
            return false;
        }

        return true;
    }
    /**
     * Run a delete statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function delete($query, $bindings = [])
    {
        $result = $this->executeQuery($query, $bindings);
        return ($result->count() != 0);
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param  string  $query
     * @return bool
     */
    public function unprepared($query)
    {
        $class = $this;
        $client = $this->getClient();

        return $this->run($query, [], function ($query) use($class, $client) {
            if ($this->pretending()) {
                return true;
            }
            $this->recordsHaveBeenModified(
                $change = $class->runQuery($client, $query, []) !== false
            );

            return $change;
        });
    }

    /**
     * Run query.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return array
     */
    public function executeQuery(string $query, array $bindings, bool $useReadPdo = false)
    {
        $class = $this;
        $client = $this->getClient();

        return $this->run($query, $bindings, function ($query, $bindings) use ($class, $client) {
            if ($this->pretending()) {
                return [];
            }
            return $class->runQuery($client, $query, $bindings);
        });
    }

    /**
     * Runs a SQL query
     *
     * @param \Tarantool\Client\Client $client
     * @param string $query
     * @param array $params
     * @return \Tarantool\Client\SqlQueryResult
     * @throws StorageModelException
     */
    private function runQuery(\Tarantool\Client\Client $client, string $sql, array $params, $operationType = '')
    {
        if (!$operationType) {
            $operationType = $this->getSqlType($sql);
        }

        if ($operationType == 'SELECT') {
            $result = $client->executeQuery($sql, ...$params);
        } else {
            $result = $client->executeUpdate($sql, ...$params);
        }

        return $result;
    }
}
