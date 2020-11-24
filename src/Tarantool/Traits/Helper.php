<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool\Traits;

trait Helper
{
    /**
     * Get type of SQL query.
     *
     * @param  string  $sql
     * @return string
     * @psalm-suppress PossiblyFalseArgument
     */
    public function getSqlType(string $sql): string
    {
        $sql = trim($sql);

        return strtoupper(substr($sql, 0, strpos($sql, ' ')));
    }
}
