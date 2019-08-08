<?php

namespace Chocofamily\Tarantool\Traits;

trait Helper
{
    /**
     * Get type of SQL query
     *
     * @param  string  $sql
     * @return string
     */
    public function getSqlType(string $sql): string
    {
        $sql = trim($sql);
        return strtoupper(substr($sql,0,strpos($sql,' ')));
    }
}