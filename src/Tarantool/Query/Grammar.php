<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar as BaseGrammar;
use Illuminate\Support\Str;

class Grammar extends BaseGrammar
{
    protected $reservedWords = [
        'migration',
        'batch',
        'id',
        'connection',
        'queue',
        'payload',
        'exception',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at',
        'failed_at',
        //all,alter,analyze,and,any,as,asc,asensitive,begin,between,binary,by,call,case,char,character,check,collate,column,commit,condition,connect,constraint,create,cross,current,current_date,current_time,current_timestamp,current_user,cursor,date,decimal,declare,default,delete,dense_rank,desc,describe,deterministic,distinct,double,drop,each,else,elseif,end,escape,except,exists,explain,fetch,float,for,foreign,from,function,get,grant,group,having,if,immediate,in,index,inner,inout,insensitive,insert,integer,intersect,into,is,iterate,join,leave,left,like,localtime,localtimestamp,loop,match,natural,not,null,of,on,or,order,out,outer,over,partition,pragma,precision,primary,procedure,range,rank,reads,recursive,references,reindex,release,rename,repeat,replace,resignal,return,revoke,right,rollback,row,row_number,rows,savepoint,select,sensitive,set,signal,smallint,specific,sql,start,system,table,then,to,transaction,trigger,union,unique,update,user,using,values,varchar,view,when,whenever,where,while,with
    ];

    /**
     * @inheritDoc
     */
    public function wrap($value, $prefixAlias = false)
    {
        $value = parent::wrap($value, $prefixAlias);

        return $value;
    }

    /**
     * @inheritDoc
     */
    protected function wrapSegments($segments)
    {
        return collect($segments)->map(function ($segment, $key) use ($segments) {
            if (count($segments) > 1) {
                if ($key == 0) {
                    return $this->wrapTable($segment);
                } else {
                    return strtoupper($this->addQuotes($segment));
                }
            } else {
                return $this->wrapValue($segment);
            }
        })->implode('.');
    }

    /**
     * @inheritDoc
     */
    protected function wrapValue($value)
    {
        if ($value === '*') {
            return $value;
        }

        if (in_array($value, $this->reservedWords)) {
            return $this->addQuotes($value);
        }

        return str_replace('"', '""', $value);
    }

    /**
     * @inheritDoc
     */
    public function wrapTable($value)
    {
        if ($this->isExpression($value)) {
            return parent::wrapTable($value);
        }
        return '"' . str_replace('"', '""', strtoupper($value)) . '"';
    }

    /**
     * @inheritDoc
     */
    public function compileInsert(Builder $query, array $values)
    {
        // Essentially we will force every insert to be treated as a batch insert which
        // simply makes creating the SQL easier for us since we can utilize the same
        // basic routine regardless of an amount of records given to us to insert.
        $table = $this->wrapTable($query->from);

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnizeCustom(array_keys(reset($values)));

        // We need to build a list of parameter place-holders of values that are bound
        // to the query. Each insert should have the exact same amount of parameter
        // bindings so we will loop through the record and parameterize them all.
        $parameters = collect($values)->map(function ($record) {
            return '(' . $this->parameterize($record) . ')';
        })->implode(', ');

        return "insert into $table ($columns) values $parameters";
    }

    /**
     * Convert an array of column names into a delimited string.
     *
     * @param array $columns
     * @return string
     */
    private function columnizeCustom(array $columns): string
    {
        $wrappedColumns = array_map([$this, 'wrap'], $columns);
        array_walk($wrappedColumns, function (&$x) {
            $x = Str::contains($x, '"') ? $x : '"' . $x . '"';
        });

        return implode(', ', $wrappedColumns);
    }

    /**
     * Add quotes to string
     * @param string $string
     * @return string
     */
    private function addQuotes(string $string): string
    {
        if ($string === '*') {
            return $string;
        }

        return '"' . str_replace('"', '""', $string) . '"';
    }
}
