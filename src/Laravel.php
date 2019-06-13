<?php

namespace Recca0120\LaravelBridge;

use BadMethodCallException;
use Exception;
use Illuminate\Container\Container as LaravelContainer;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Fluent;
use Psr\Container\ContainerInterface;
use Recca0120\LaravelBridge\Exceptions\EntryNotFoundException;
use Recca0120\LaravelBridge\Concerns\SetupLaravel;
use Recca0120\LaravelBridge\Concerns\SetupTracy;

/**
 * @mixin LaravelContainer
 */
class Laravel implements ContainerInterface
{
    use SetupLaravel;
    use SetupTracy;

    /**
     * @var static
     */
    public static $instance;

    /**
     * @var array
     */
    public $aliases = [
        'View' => View::class,
    ];

    /**
     * @var App
     */
    private $app;

    /**
     * @var bool
     */
    private $bootstrapped = false;

    public function __construct()
    {
        $this->app = new App();
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->app, $method)) {
            return call_user_func_array([$this->app, $method], $arguments);
        }

        throw new BadMethodCallException("Undefined method '$method'");
    }

    /**
     * @return static
     */
    public function bootstrap()
    {
        $this->bootstrapped = true;

        $this->app->singleton('request', function () {
            return Request::capture();
        });

        $this->app->singleton('config', Fluent::class);
        $this->app->singleton('events', Dispatcher::class);
        $this->app->singleton('files', Filesystem::class);

        Facade::setFacadeApplication($this->app);

        foreach ($this->aliases as $alias => $class) {
            if (!class_exists($alias)) {
                class_alias($class, $alias);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isBootstrapped()
    {
        return $this->bootstrapped;
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return $this->app->bound($id);
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        try {
            return $this->app->make($id);
        } catch (Exception $e) {
            if ($this->has($id)) {
                throw $e;
            }

            throw new EntryNotFoundException;
        }
    }

    /**
     * getApp.
     *
     * @method getApp
     *
     * @return LaravelContainer
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->app->make('request');
    }

    /**
     * @return Dispatcher
     */
    public function getEvents()
    {
        return $this->app->make('events');
    }

    /**
     * @return Fluent
     */
    public function getConfig()
    {
        return $this->app->make('config');
    }

    /**
     * @param bool $is
     *
     * @return static
     */
    public function setupRunningInConsole($is = true)
    {
        $this->app['runningInConsole'] = $is;

        return $this;
    }

    /**
     * @return static
     * @deprecated use getInstance()
     */
    public static function instance()
    {
        return static::getInstance();
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        if (!static::$instance->isBootstrapped()) {
            static::$instance->bootstrap();
        }

        return static::$instance;
    }

    /**
     * Flash instance
     */
    public static function flashInstance()
    {
        $instance = static::getInstance();

        $instance->flush();
        $instance->bootstrapped = false;
    }
}
