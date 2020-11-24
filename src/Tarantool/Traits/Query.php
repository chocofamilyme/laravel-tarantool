<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool\Traits;

use Closure;
use Exception;
use Illuminate\Database\QueryException;
use Tarantool\Client\Client;
use Tarantool\Client\SqlQueryResult;
use Tarantool\Client\SqlUpdateResult;

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
    public function select($query, $bindings = [], $useReadPdo = false)
    {
        /** @var SqlQueryResult $result */
        $result = $this->executeQuery($query, $bindings, $useReadPdo);

        return $this->getDataWithKeys($result);
    }

    /**
     * Run an insert statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return array
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function insert($query, $bindings = [])
    {
        return $this->executeQuery($query, $bindings);
    }

    /**
     * Run an update statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @return array
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function update($query, $bindings = [])
    {
        return $this->executeQuery($query, $bindings);
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
        /** @var SqlQueryResult $result */
        $result = $this->executeQuery($query, $bindings);

        return (int) ($result->count() !== 0);
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

        return $this->run($query, [], function ($query) use ($class, $client) {
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
     * @throws QueryException
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        // To execute the statement, we'll simply call the callback, which will actually
        // run the SQL against the PDO connection. Then we can calculate the time it
        // took to execute and log the query SQL, bindings and time in our memory.
        try {
            $result = $this->runQuery($this->getClient(), $query, $bindings);
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
     * Runs a SQL query.
     *
     * @param Client $client
     * @param string $sql
     * @param array $params
     * @param string $operationType
     * @return SqlQueryResult|SqlUpdateResult
     */
    private function runQuery(Client $client, string $sql, array $params, $operationType = '')
    {
        if (! $operationType) {
            $operationType = $this->getSqlType($sql);
        }

        if ($operationType === 'SELECT') {
            $result = $client->executeQuery($sql, ...$params);
        } else {
            $result = $client->executeUpdate($sql, ...$params);
        }

        return $result;
    }

    /**
     * @param  SqlQueryResult $result
     * @return array
     */
    private function getDataWithKeys(SqlQueryResult $result) : array
    {
        $data = iterator_to_array($result);

        return array_map(static function ($item) {
            return array_change_key_case($item, CASE_LOWER);
        }, $data);
    }
}
