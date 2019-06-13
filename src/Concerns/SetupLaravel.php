<?php

namespace Recca0120\LaravelBridge\Concerns;

use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\View\ViewServiceProvider;
use PDO;

trait SetupLaravel
{
    /**
     * setup user define provider
     *
     * @param callable $callable The callable can return the instance of ServiceProvider
     *
     * @return static
     */
    public function setupCallableProvider(callable $callable)
    {
        $serviceProvider = $callable($this->app);
        $serviceProvider->register();

        if (method_exists($serviceProvider, 'boot') === true) {
            $this->call([$serviceProvider, 'boot']);
        }

        return $this;
    }

    /**
     * @param array $connections
     * @param string $default
     * @param int $fetch
     *
     * @return static
     */
    public function setupDatabase(array $connections, $default = 'default', $fetch = PDO::FETCH_CLASS)
    {
        return $this->setupCallableProvider(function ($app) use ($connections, $default, $fetch) {
            $app['config']['database.connections'] = $connections;
            $app['config']['database.default'] = $default;
            $app['config']['database.fetch'] = $fetch;

            return new DatabaseServiceProvider($app);
        });
    }

    /**
     * @param string $locale
     *
     * @return static
     */
    public function setupLocale($locale)
    {
        $this->app['config']['app.locale'] = $locale;

        return $this;
    }

    /**
     * @return static
     */
    public function setupPagination()
    {
        return $this->setupCallableProvider(function ($app) {
            return new PaginationServiceProvider($app);
        });
    }

    /**
     * @param string $langPath
     *
     * @return static
     */
    public function setupTranslator($langPath)
    {
        return $this->setupCallableProvider(function ($app) use ($langPath) {
            $app->instance('path.lang', $langPath);

            return new TranslationServiceProvider($app);
        });
    }

    /**
     * @param string|array $viewPath
     * @param string $compiledPath
     *
     * @return static
     */
    public function setupView($viewPath, $compiledPath)
    {
        return $this->setupCallableProvider(function ($app) use ($viewPath, $compiledPath) {
            $app['config']['view.paths'] = is_array($viewPath) ? $viewPath : [$viewPath];
            $app['config']['view.compiled'] = $compiledPath;

            return new ViewServiceProvider($app);
        });
    }
}
