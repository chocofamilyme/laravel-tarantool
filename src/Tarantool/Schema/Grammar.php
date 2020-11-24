<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool\Schema;

use Chocofamily\Tarantool\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar as BaseGrammar;
use Illuminate\Support\Fluent;

class Grammar extends BaseGrammar
{
    /**
     * The possible column serials.
     *
     * @var array
     */
    protected $serials = ['integer'];

    /**
     * The possible column modifiers.
     *
     * @var array
     */
    protected $modifiers = ['Default', 'Nullable'];

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param mixed $table
     * @return string
     */
    public function wrapTable($table)
    {
        $value = parent::wrapTable($table);

        return strtoupper($value);
    }

    /**
     * Compile the query to determine if a table exists.
     *
     * @return string
     */
    public function compileTableExists()
    {
        return 'select * from "_space" where "name" = ?';
    }

    /**
     * @param array $columns
     * @return string
     * @psalm-suppress all
     */
    private function autoAddPrimaryKey(array $columns)
    {
        if (! empty($columns)) {
            $idColumnIndex = false;
            $primaryKeyExist = false;
            $autoIncrementExist = false;

            foreach ($columns as $index => $column) {
                if (! $primaryKeyExist) {
                    $searchPM = strripos($column, 'PRIMARY KEY');
                    if ($searchPM != false) {
                        $primaryKeyExist = true;
                    }
                }

                if (is_bool($idColumnIndex) && $idColumnIndex === false) {
                    $searchID = strripos($column, '"id"');
                    if ($searchID !== false) {
                        $idColumnIndex = $index;
                    }
                }

                if (! $autoIncrementExist) {
                    $searchAutoIncrement = strripos($column, 'autoincrement');
                    if ($searchAutoIncrement !== false) {
                        $autoIncrementExist = true;
                    }
                }
            }

            if (is_int($idColumnIndex) and $idColumnIndex !== false) {
                $textToAdd = '';

                if ($primaryKeyExist === false) {
                    $textToAdd = $textToAdd.' PRIMARY KEY';
                }

                if ($autoIncrementExist === false) {
                    $textToAdd = $textToAdd.' AUTOINCREMENT';
                }

                $columns[$idColumnIndex] = $columns[$idColumnIndex].$textToAdd;
            }
        }

        return implode(', ', $columns);
    }

    /**
     * Compile a create table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent $command
     * @param Connection $connection
     * @return string
     */
    public function compileCreate(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        $columns = $this->autoAddPrimaryKey($this->getColumns($blueprint));
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->wrapTable($blueprint)." ($columns)";

        return $sql;
    }

    /**
     * Compile a drop table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileDrop(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table '.$this->wrapTable($blueprint);
    }

    /**
     * Compile a primary key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compilePrimary(Blueprint $blueprint, Fluent $command)
    {
        $command->name(null);

        return $this->compileKey($blueprint, $command, 'PRIMARY KEY');
    }

    /**
     * Compile a unique key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileUnique(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->columnize($command->columns);
        $table = $this->wrapTable($blueprint);

        return 'CREATE UNIQUE INDEX '.strtoupper(substr($command->index, 0, 31))." ON {$table} ($columns)";
    }

    /**
     * Compile a plain index key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndex(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->columnize($command->columns);
        $table = $this->wrapTable($blueprint);

        return 'CREATE INDEX '.strtoupper(substr($command->index, 0, 31))." ON {$table} ($columns)";
    }

    /**
     * Compile an index creation command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @param  string  $type
     * @return string
     */
    protected function compileKey(Blueprint $blueprint, Fluent $command, $type)
    {
        $columns = $this->columnize($command->columns);
        $table = $this->wrapTable($blueprint);

        return "alter table {$table} add {$type} ($columns)";
    }

    /**
     * Compile a foreign key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    /*
    public function compileForeign(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);
        $on = $this->wrapTable($command->on);
        // We need to prepare several of the elements of the foreign key definition
        // before we can create the SQL, such as wrapping the tables and convert
        // an array of columns to comma-delimited strings for the SQL queries.
        $columns = $this->columnize($command->columns);
        $onColumns = $this->columnize((array) $command->references);
        $sql = "alter table {$table} add constraint ".strtoupper(substr($command->index, 0, 31))." ";
        $sql .= "foreign key ({$columns}) references {$on} ({$onColumns})";
        // Once we have the basic foreign key creation statement constructed we can
        // build out the syntax for what should happen on an update or delete of
        // the affected columns, which will get something like "cascade", etc.
        if ( ! is_null($command->onDelete))
        {
            $sql .= " on delete {$command->onDelete}";
        }
        if ( ! is_null($command->onUpdate))
        {
            $sql .= " on update {$command->onUpdate}";
        }
        return $sql;
    }
    */

    /**
     * Compile a drop foreign key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileDropForeign(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        return "alter table {$table} drop constraint {$command->index}";
    }

    /**
     * Get the SQL for a nullable column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $column
     * @return string|null
     */
    protected function modifyNullable(Blueprint $blueprint, Fluent $column)
    {
        return $column->nullable ? '' : ' not null';
    }

    /**
     * Get the SQL for a default column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $column
     * @return string|null
     */
    protected function modifyDefault(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->default)) {
            return ' default '.$this->getDefaultValue($column->default);
        }

        return null;
    }

    /**
     * Create the column definition for a char type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeChar(Fluent $column)
    {
        return 'TEXT';
    }

    /**
     * Create the column definition for a string type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeString(Fluent $column)
    {
        return 'VARCHAR ('.$column->length.')';
    }

    /**
     * Create the column definition for a text type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeText(Fluent $column)
    {
        return 'TEXT';
    }

    /**
     * Create the column definition for a medium text type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeMediumText(Fluent $column)
    {
        return 'TEXT';
    }

    /**
     * Create the column definition for a long text type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeLongText(Fluent $column)
    {
        return 'TEXT';
    }

    /**
     * Create the column definition for a integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeInteger(Fluent $column)
    {
        return 'INTEGER';
    }

    /**
     * Create the column definition for a big integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeBigInteger(Fluent $column)
    {
        return 'INTEGER';
    }

    /**
     * Create the column definition for a medium integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeMediumInteger(Fluent $column)
    {
        return 'INTEGER';
    }

    /**
     * Create the column definition for a tiny integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTinyInteger(Fluent $column)
    {
        return 'INTEGER';
    }

    /**
     * Create the column definition for a small integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeSmallInteger(Fluent $column)
    {
        return 'INTEGER';
    }

    /**
     * Create the column definition for a float type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeFloat(Fluent $column)
    {
        return 'NUMBER';
    }

    /**
     * Create the column definition for a double type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDouble(Fluent $column)
    {
        return 'NUMBER';
    }

    /**
     * Create the column definition for a decimal type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDecimal(Fluent $column)
    {
        return 'NUMBER';
    }

    /**
     * Create the column definition for a boolean type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeBoolean(Fluent $column)
    {
        return 'SCALAR';
    }

    /**
     * Create the column definition for an enum type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeEnum(Fluent $column)
    {
        return 'TEXT';
    }

    /**
     * Create the column definition for a json type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeJson(Fluent $column)
    {
        return 'TEXT';
    }

    /**
     * Create the column definition for a date type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDate(Fluent $column)
    {
        return 'VARCHAR (10)';
    }

    /**
     * Create the column definition for a date-time type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDateTime(Fluent $column)
    {
        return 'VARCHAR (30)';
    }

    /**
     * Create the column definition for a time type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTime(Fluent $column)
    {
        return 'VARCHAR (10)';
    }

    /**
     * Create the column definition for a timestamp type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTimestamp(Fluent $column)
    {
        return 'VARCHAR (200)';
    }

    /**
     * Create the column definition for a binary type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeBinary(Fluent $column)
    {
        return 'SCALAR';
    }
}
