# laravel-tarantool

Установка
------------

Установка с помощью composer:

```
composer require chocofamily/laravel-tarantool
```

Конфигурация
-------------

Изменить стандартное подключение к базе в `config/database.php`:

```php
'default' => env('DB_CONNECTION', 'tarantool'),
```

Добавить конфигурацию подключения к Tarantool:

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

Можно подключить альтернативным способом через строку:

```php
'tarantool' => [
    'driver'   => 'tarantool',
    'dsn' => env('DB_DSN'),
    'database' => env('DB_DATABASE'),
],
```