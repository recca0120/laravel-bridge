<?php

namespace Recca0120\LaravelBridge;

use BadMethodCallException;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container as LaravelContainer;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\View;
use Illuminate\View\ViewServiceProvider;
use PDO;
use Recca0120\LaravelTracy\Tracy;

/**
 * @mixin LaravelContainer
 */
class Laravel
{
    /**
     * $aliases.
     *
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
     * $instance.
     *
     * @var static
     */
    public static $instance;

    /**
     * __construct.
     *
     * @method __construct
     */
    public function __construct()
    {
        $this->app = new App();

        $this->app->singleton('request', function () {
            return Request::capture();
        });

        $this->app->singleton('config', ConfigRepository::class);
        $this->app->singleton('events', Dispatcher::class);
        $this->app->singleton('files', Filesystem::class);

        Facade::setFacadeApplication($this->app);

        foreach ($this->aliases as $alias => $class) {
            if (!class_exists($alias)) {
                class_alias($class, $alias);
            }
        }
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->app, $method)) {
            return call_user_func_array([$this->app, $method], $arguments);
        }

        throw new BadMethodCallException("Undefined method '$method'");
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
     * getRequest.
     *
     * @method getRequest
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->app->make('request');
    }

    /**
     * getEvents.
     *
     * @method getEvents
     *
     * @return Dispatcher
     */
    public function getEvents()
    {
        return $this->app->make('dispatcher');
    }

    /**
     * getConfig.
     *
     * @method getConfig
     *
     * @return ConfigRepository
     */
    public function getConfig()
    {
        return $this->app->make('config');
    }

    /**
     * @param bool $is
     */
    public function setupRunningInConsole($is = true)
    {
        $this->app['runningInConsole'] = $is;

        return $this;
    }

    /**
     * setupView.
     *
     * @method setupView
     *
     * @param string|array $viewPath
     * @param string $compiledPath
     *
     * @return static
     */
    public function setupView($viewPath, $compiledPath)
    {
        return $this->setupCallableProvider(function ($app) use ($viewPath, $compiledPath) {
            $config = $this->getConfig();

            $config->set([
                'view.paths' => is_array($viewPath) ? $viewPath : [$viewPath],
                'view.compiled' => $compiledPath,
            ]);

            return new ViewServiceProvider($app);
        });
    }

    /**
     * setupDatabase.
     *
     * @method setupDatabase
     *
     * @param array $connections
     * @param string $default
     * @param int $fetch
     *
     * @return static
     */
    public function setupDatabase(array $connections, $default = 'default', $fetch = PDO::FETCH_CLASS)
    {
        return $this->setupCallableProvider(function ($app) use ($connections, $default, $fetch) {
            $config = $this->getConfig();

            $config->set([
                'database.connections' => $connections,
                'database.default' => $default,
                'database.fetch' => $fetch,
            ]);

            return new DatabaseServiceProvider($app);
        });
    }

    /**
     * setupPagination.
     *
     * @method setupPagination
     *
     * @return static
     */
    public function setupPagination()
    {
        return $this->setupCallableProvider(function ($app) {
            return new PaginationServiceProvider($app);
        });
    }

    /**
     * setupTracy.
     *
     * @method setupTracy
     *
     * @param array $config
     * @return static
     */
    public function setupTracy($config = [])
    {
        $tracy = Tracy::instance($config);
        $databasePanel = $tracy->getPanel('database');
        $this->getEvents()->listen(QueryExecuted::class, function ($event) use ($databasePanel) {
            $sql = $event->sql;
            $bindings = $event->bindings;
            $time = $event->time;
            $name = $event->connectionName;
            $pdo = $event->connection->getPdo();

            $databasePanel->logQuery($sql, $bindings, $time, $name, $pdo);
        });

        return $this;
    }

    /**
     * setup user define provider
     *
     * @param callable $callable The callable can return the instance of ServiceProvider
     * @return static
     */
    public function setupCallableProvider(callable $callable)
    {
        $this->bootServiceProvider($callable($this->app));

        return $this;
    }

    protected function bootServiceProvider($serviceProvider)
    {
        $serviceProvider->register();
        if (method_exists($serviceProvider, 'boot') === true) {
            $this->call([$serviceProvider, 'boot']);
        }
    }

    /**
     * instance.
     *
     * @method instance
     *
     * @return static
     * @deprecated use createInstance()
     */
    public static function instance()
    {
        return static::createInstance();
    }

    /**
     * create instance.
     *
     * @method instance
     *
     * @return static
     */
    public static function createInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
