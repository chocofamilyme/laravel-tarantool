<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool\Console;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;

class QueueTarantoolFunctionCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'queue:tarantool-function';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a tarantool function for the queue jobs database table';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * Create a new queue job table command instance.
     *
     * @param Filesystem $files
     * @param Composer $composer
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $functionName = 'get_active_job';

        $this->replaceMigration(
            $this->createBaseMigration($functionName), $functionName, Str::studly($functionName)
        );

        $this->info('Migration created successfully!');

        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the function.
     *
     * @param string $functionName
     * @return string
     */
    protected function createBaseMigration(string $functionName)
    {
        return $this->laravel['migration.creator']->create(
            'create_' . $functionName . '_function', $this->laravel->databasePath() . '/migrations'
        );
    }

    /**
     * Replace the generated migration with the job table stub.
     *
     * @param string $path
     * @param string $functionName
     * @param string $className
     * @return void
     * @throws FileNotFoundException
     */
    protected function replaceMigration(string $path, string $functionName, string $className)
    {
        $stub = str_replace(
            ['{{functionName}}', '{{className}}', '{{table}}'],
            [$functionName, $className, strtoupper($this->laravel['config']['queue.connections.database.table'])],
            $this->files->get(__DIR__ . '/stubs/function.stub')
        );

        $this->files->put($path, $stub);
    }
}
