# Laravel Tarantool

This package adds functionalities to the Eloquent model and Query builder for Tarantool, using the original Laravel API. *This library extends the original Laravel classes, so it uses exactly the same methods.*

Installation
------------
#### Laravel Version Compatibility

Laravel         | Package
:---------------|:---------------
 5.8.x          | 0.1.9
 6.x            | 0.1.9
 7.x            | 0.1.9
 8.x            | 1.*
 8.x  + queue   | 1.1.*


#### Via Composer

```
composer require chocofamilyme/laravel-tarantool
```

### Laravel

In case your Laravel version does NOT autoload the packages, add the service provider to `config/app.php`:

```php
Chocofamily\Tarantool\ServiceProvider::class,
```

### Lumen

For usage with [Lumen](http://lumen.laravel.com), add the service provider in `bootstrap/app.php`. In this file, you will also need to enable Eloquent. You must however ensure that your call to `$app->withEloquent();` is **below** where you have registered the `ServiceProvider`:

```php
$app->register(Chocofamily\Tarantool\ServiceProvider::class);

$app->withEloquent();
```

The service provider will register a Tarantool database extension with the original database manager. There is no need to register additional facades or objects.

When using Tarantool connections, Laravel will automatically provide you with the corresponding Tarantool objects.

Configuration
-------------

You can use Tarantool either as the main database, either as a side database. To do so, add a new `tarantool` connection to `config/database.php`:

```php
'tarantool' => [
    'driver'   => 'tarantool',
    'host'     => env('DB_HOST', '127.0.0.1'),
    'port'     => env('DB_PORT', 3301),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'driver_oprions' => [
        'connection_type'     => env('DB_CONNECTION_TYPE', 'tcp')
    ],
    'options'  => [
        'connect_timeout' => 5,
        'max_retries' => 3
    ]
],
```

Set tarantool as main database

```php
'default' => env('DB_CONNECTION', 'tarantool'),
```

You can also configure connection with dsn string:

```php
'tarantool' => [
    'driver'   => 'tarantool',
    'dsn' => env('DB_DSN'),
    'database' => env('DB_DATABASE'),
],
```

### Queues
If you want to use Tarantool as your database backend, change the driver in `config/queue.php`:

```php
'connections' => [
    'database' => [
        'driver' => 'tarantool',
        'table' => 'jobs',
        'queue' => 'default',
        'expire' => 60,
    ],
],
```
Also you need run the console command:
```
php artisan queue:tarantool-function
```
This command create migration file with some Tarantool function.
**You need apply this migration!**

If you want to use Tarantool to handle failed jobs, change the database in `config/queue.php` and add the service provider:
```php
Chocofamily\Tarantool\TarantoolQueueServiceProvider::class,
```

```php
'failed' => [
    'driver' => 'tarantool',
    // You can also specify your jobs specific database created on config/database.php
    'database' => 'tarantool',
    'table' => 'failed_jobs',
],
```

#### Laravel specific

Add the service provider in `config/app.php`:

```php
Chocofamily\Tarantool\TarantoolQueueServiceProvider::class,
```

#### Lumen specific

With [Lumen](http://lumen.laravel.com), add the service provider in `bootstrap/app.php`. You must however ensure that you add the following **after** the `TarantoolQueueServiceProvider` registration.

```php
$app->make('queue');

$app->register(Chocofamily\Tarantool\TarantoolQueueServiceProvider::class);
```