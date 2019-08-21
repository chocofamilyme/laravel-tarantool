<?php

namespace Chocofamily\Tarantool\Traits;

use Closure;
use Exception;
use Illuminate\Database\QueryException;

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
        $result = $this->executeQuery($query, $bindings, $useReadPdo);
        return $this->getDataWithKeys($result->getData(), $result->keys);
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
        return $result;
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
        return $result;
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
     * Run a SQL statement.
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Illuminate\Database\QueryException
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        // To execute the statement, we'll simply call the callback, which will actually
        // run the SQL against the PDO connection. Then we can calculate the time it
        // took to execute and log the query SQL, bindings and time in our memory.
        try {
            //var_dump($query);
            $result = $this->runQuery($this->getClient(), $query, $bindings);
            //dd($this->getDataWithKeys($result->getData(), $result->keys));
            //dd($result);
        }

            // If an exception occurs when attempting to run a query, we'll format the error
            // message to include the bindings with SQL, which will make this exception a
            // lot more helpful to the developer instead of just the database's errors.
        catch (Exception $e) {
            throw new QueryException(
                $query, $this->prepareBindings($bindings), $e
            );
        }

        return $result;
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

    /**
     * @param  array    $data
     * @param  array    $keys
     * @return array
     */
    private function getDataWithKeys($data = [], $keys = []) : array
    {
        $result = [];

        foreach ($data as $item) {
            $result[] = array_combine($keys, $item);
        }

        return $result;
    }
}
