<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor as BaseProcessor;
use Tarantool\Client\SqlUpdateResult;

class Processor extends BaseProcessor
{
    /**
     * Process an  "insert get ID" query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $sql
     * @param  array   $values
     * @param  string|null  $sequence
     * @return int
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        /** @var SqlUpdateResult $result */
        $result = $query->getConnection()->insert($sql, $values);
        $id = $result->getAutoincrementIds()[0];

        return is_numeric($id) ? (int) $id : $id;
    }
}
