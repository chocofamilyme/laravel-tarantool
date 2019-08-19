<?php

namespace Chocofamily\Tarantool\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar as BaseGrammar;

class Grammar extends BaseGrammar
{
    protected $reservedWords = [
        'migration',
        'batch',
        //all,alter,analyze,and,any,as,asc,asensitive,begin,between,binary,by,call,case,char,character,check,collate,column,commit,condition,connect,constraint,create,cross,current,current_date,current_time,current_timestamp,current_user,cursor,date,decimal,declare,default,delete,dense_rank,desc,describe,deterministic,distinct,double,drop,each,else,elseif,end,escape,except,exists,explain,fetch,float,for,foreign,from,function,get,grant,group,having,if,immediate,in,index,inner,inout,insensitive,insert,integer,intersect,into,is,iterate,join,leave,left,like,localtime,localtimestamp,loop,match,natural,not,null,of,on,or,order,out,outer,over,partition,pragma,precision,primary,procedure,range,rank,reads,recursive,references,reindex,release,rename,repeat,replace,resignal,return,revoke,right,rollback,row,row_number,rows,savepoint,select,sensitive,set,signal,smallint,specific,sql,start,system,table,then,to,transaction,trigger,union,unique,update,user,using,values,varchar,view,when,whenever,where,while,with
    ];

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param  \Illuminate\Database\Query\Expression|string  $value
     * @param  bool    $prefixAlias
     * @return string
     */
    public function wrap($value, $prefixAlias = false)
    {
        $value = parent::wrap($value, $prefixAlias);
        return $value;
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue($value)
    {
        if ($value === '*') {
            return $value;
        }

        if (in_array($value, $this->reservedWords)) {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return str_replace('"', '""', $value);
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    public function wrapTable($value)
    {
        return '"'.str_replace('"', '""', strtoupper($value)).'"';
    }
}
