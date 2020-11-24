# Laravel Tarantool

This package adds functionalities to the Eloquent model and Query builder for Tarantool, using the original Laravel API. *This library extends the original Laravel classes, so it uses exactly the same methods.*

Installation
------------
#### Laravel Version Compatibility

Laravel  | Package
:---------|:----------
 5.8.x    | 0.1.9
 6.x      | 0.1.9
 7.x      | 0.1.9
 8.x      | 1.x


#### Via Composer

```
composer require chocofamilyme/laravel-tarantool
```

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