<?php

namespace Recca0120\LaravelBridge;

use Illuminate\Container\Container;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Fluent;
use Illuminate\View\ViewServiceProvider;
use PDO;
use Recca0120\LaravelTracy\Tracy;

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
     * @var \Illuminate\Container\Container
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
        $this->app = new Container();
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
        $viewServiceProvider->register();
        $viewServiceProvider->boot();

        return $this;
    }

    /**
     * setupDatabase.
     *
     * @method setupDatabase
     *
     * @param array  $connections
     * @param string $default
     * @param int    $fetch
     *
     * @return static
     */
    public function setupDatabase(array $connections, $default = 'default', $fetch = PDO::FETCH_CLASS)
    {
        $this->config['database.fetch'] = PDO::FETCH_CLASS;
        $this->config['database.default'] = $default;
        $this->config['database.connections'] = $connections;

        $databaseServiceProvider = new DatabaseServiceProvider($this->app);
        $databaseServiceProvider->register();
        $databaseServiceProvider->boot();

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
        $paginationServiceProvider->register();
        $paginationServiceProvider->boot();

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
