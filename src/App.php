<?php

namespace Recca0120\LaravelBridge;

use Illuminate\Container\Container;

class App extends Container
{
    /**
     * @return bool
     * @see https://github.com/laravel/framework/blob/5.8/src/Illuminate/Foundation/Application.php
     */
    public function runningInConsole()
    {
        if (isset($this['runningInConsole'])) {
            return (bool) $this['runningInConsole'];
        }

        if (isset($_ENV['APP_RUNNING_IN_CONSOLE'])) {
            return $_ENV['APP_RUNNING_IN_CONSOLE'] === 'true';
        }

        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }
}
