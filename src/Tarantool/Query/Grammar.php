<?php

namespace Chocofamily\Tarantool\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar as BaseGrammar;

class Grammar extends BaseGrammar
{
    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue($value)
    {
        if ($value !== '*') {
            return str_replace('"', '""', $value);
        }

        return $value;
    }
}
