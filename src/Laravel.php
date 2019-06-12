<?php

namespace Recca0120\LaravelBridge;

use PDO;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Illuminate\Events\Dispatcher;
use Recca0120\LaravelTracy\Tracy;
use Illuminate\Support\Facades\View;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\ViewServiceProvider;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Pagination\PaginationServiceProvider;

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
     * $app.
     *
     * @var App
     */
    public $app;

    /**
     * $request.
     *
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * $dispatcher.
     *
     * @var \Illuminate\Events\Dispatcher
     */
    public $dispatcher;

    /**
     * $config.
     *
     * @var \Illuminate\Support\Fluent
     */
    public $config;

    /**
     * $files.
     *
     * @var \Illuminate\Filesystem\Filesystem;
     */
    public $files;

    /**
     * $instance.
     *
     * @var self
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
        $this->request = Request::capture();
        $this->dispatcher = new Dispatcher();
        $this->config = new Fluent();
        $this->files = new Filesystem();

        $this->app['request'] = $this->request;
        $this->app['events'] = $this->dispatcher;
        $this->app['config'] = $this->config;
        $this->app['files'] = $this->files;

        Facade::setFacadeApplication($this->app);

        foreach ($this->aliases as $alias => $class) {
            if (class_exists($alias) === true) {
                continue;
            }
            class_alias($class, $alias);
        }
    }

    /**
     * getApp.
     *
     * @method getApp
     *
     * @return \Illuminate\Container\Container
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
     * @return \Illuminate\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * getEvents.
     *
     * @method getEvents
     *
     * @return \Illuminate\Events\Dispatcher
     */
    public function getEvents()
    {
        return $this->dispatcher;
    }

    /**
     * getConfig.
     *
     * @method getConfig
     *
     * @return \Illuminate\Support\Fluent
     */
    public function getConfig()
    {
        return $this->config;
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
     * @param string $viewPath
     * @param string $compiledPath
     *
     * @return static
     */
    public function setupView($viewPath, $compiledPath)
    {
        $this->config['view.paths'] = is_array($viewPath) ? $viewPath : [$viewPath];
        $this->config['view.compiled'] = $compiledPath;
        $viewServiceProvider = new ViewServiceProvider($this->app);
        $this->bootServiceProvider($viewServiceProvider);

        return $this;
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
        $this->config['database.fetch'] = PDO::FETCH_CLASS;
        $this->config['database.default'] = $default;
        $this->config['database.connections'] = $connections;

        $databaseServiceProvider = new DatabaseServiceProvider($this->app);
        $this->bootServiceProvider($databaseServiceProvider);

        return $this;
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
        $paginationServiceProvider = new PaginationServiceProvider($this->app);
        $this->bootServiceProvider($paginationServiceProvider);

        return $this;
    }

    /**
     * setupTracy.
     *
     * @method setupTracy
     *
     * @return static
     */
    public function setupTracy($config = [])
    {
        $tracy = Tracy::instance($config);
        $databasePanel = $tracy->getPanel('database');
        $this->dispatcher->listen(QueryExecuted::class, function ($event) use ($databasePanel) {
            $sql = $event->sql;
            $bindings = $event->bindings;
            $time = $event->time;
            $name = $event->connectionName;
            $pdo = $event->connection->getPdo();

            $databasePanel->logQuery($sql, $bindings, $time, $name, $pdo);
        });
    }

    /**
     * setup user define provider.
     *
     * @param callable $callable The callable can return the instance of ServiceProvider
     * @return static
     */
    public function setupCustomProvider(callable $callable)
    {
        $this->bootServiceProvider($callable($this->app));

        return $this;
    }

    protected function bootServiceProvider($serviceProvider)
    {
        $serviceProvider->register();
        if (method_exists($serviceProvider, 'boot') === true) {
            $this->app->call([$serviceProvider, 'boot']);
        }
    }

    /**
     * instance.
     *
     * @method instance
     *
     * @return static
     */
    public static function instance()
    {
        if (is_null(static::$instance) === true) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
