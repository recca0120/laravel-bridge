<?php

namespace Recca0120\LaravelBridge;

use Illuminate\Container\Container as LaravelContainer;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class App extends LaravelContainer
{
    private $serviceProviders = [];

    /**
     * For workaround
     *
     * @return bool
     * @see https://github.com/laravel/framework/blob/5.8/src/Illuminate/Foundation/Application.php
     */
    public function runningInConsole()
    {
        if (isset($this['runningInConsole'])) {
            return (bool)$this['runningInConsole'];
        }

        if (isset($_ENV['APP_RUNNING_IN_CONSOLE'])) {
            return $_ENV['APP_RUNNING_IN_CONSOLE'] === 'true';
        }

        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * @param string $class
     *
     * @return ServiceProvider
     */
    public function register($class)
    {
        /** @var ServiceProvider $provider */
        $provider = new $class($this);

        $provider->register();

        if (property_exists($provider, 'bindings')) {
            foreach ($provider->bindings as $key => $value) {
                $this->bind($key, $value);
            }
        }

        if (property_exists($provider, 'singletons')) {
            foreach ($provider->singletons as $key => $value) {
                $this->singleton($key, $value);
            }
        }

        $this->serviceProviders[$class] = $provider;

        return $provider;
    }

    public function boot()
    {
        array_walk($this->serviceProviders, function ($provider) {
            if (method_exists($provider, 'boot')) {
                $this->call([$provider, 'boot']);
            }
        });
    }

    /**
     * @param string|mixed $provider
     * @return mixed
     */
    public function getProvider($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        $found = Arr::where($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        });

        $values = array_values($found);

        return isset($values[0]) ? $values[0] : null;
    }
}
