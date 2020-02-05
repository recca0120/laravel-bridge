<p align="center"><img src="docs/logo.svg"></p>

<p align="center">
<a href="https://travis-ci.org/recca0120/laravel-bridge"><img src="https://travis-ci.org/recca0120/laravel-bridge.svg?branch=master" alt="Build Status"></a>
<a href="https://coveralls.io/github/recca0120/laravel-bridge"><img src="https://coveralls.io/repos/github/recca0120/laravel-bridge/badge.svg?branch=master" alt="Coverage Status"></a>
<a href="https://packagist.org/packages/recca0120/laravel-bridge"><img src="https://poser.pugx.org/recca0120/laravel-bridge/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/recca0120/laravel-bridge"><img src="https://poser.pugx.org/recca0120/laravel-bridge/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/recca0120/laravel-bridge"><img src="https://poser.pugx.org/recca0120/laravel-bridge/v/unstable" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/recca0120/laravel-bridge"><img src="https://poser.pugx.org/recca0120/laravel-bridge/license" alt="License"></a>
<a href="https://packagist.org/packages/recca0120/laravel-bridge"><img src="https://poser.pugx.org/recca0120/laravel-bridge/d/monthly" alt="Monthly Downloads"></a>
<a href="https://packagist.org/packages/recca0120/laravel-bridge"><img src="https://poser.pugx.org/recca0120/laravel-bridge/d/daily" alt="Daily Downloads"></a>
</p>


## Installation

Add Presenter to your composer.json file:

```json
"require": {
    "recca0120/laravel-bridge": "^1.0.0"
}
```

> Require `illuminate/translation` when using Pagination.

Now, run a composer update on the command line from the root of your project:

```
composer update
```

> **NOTICE**: NOT support Laravel 5.4.*

## How to use

setup

```php
use Recca0120\LaravelBridge\Laravel;

require __DIR__.'/vendor/autoload.php';

$connections = [
    'default' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'port'      => 3306,
        'database'  => 'forge',
        'username'  => 'forge',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
        'strict'    => false,
        'engine'    => null,
    ],
];

Laravel::instance()
    ->setupView(__DIR__.'/views/', __DIR__.'/views/cache/compiled/')
    ->setupDatabase($connections)
    ->setupPagination()
    ->setupTracy([
        'showBar' => true
    ]);
```

eloquent

```php
class User extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [
       'name',
       'email',
       'password',
   ];
}

var_dump(User::all());
```

view

view.blade.php

```php
@foreach ($rows as $row)
    {{ $row }};
@endforeach
```

view

```php
echo View::make('view', ['rows' => [1, 2, 3]]);
```

### Example

[Laraigniter](https://github.com/recca0120/laraigniter)
