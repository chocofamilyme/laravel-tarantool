<?php

namespace Chocofamily\Tarantool\Traits;

trait Helper
{
    public function getSqlType(string $sql): string
    {
        $sql = trim($sql);
        return strtoupper(substr($sql,0,strpos($sql,' ')));
    }
}