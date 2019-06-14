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
     * Setup all LaravelProvider
     */
    public function setupLaravelProviders()
    {
        collect([
            'Illuminate\Auth\AuthServiceProvider',
            'Illuminate\Broadcasting\BroadcastServiceProvider',
            'Illuminate\Bus\BusServiceProvider',
            'Illuminate\Cache\CacheServiceProvider',
            'Illuminate\Foundation\Providers\ConsoleSupportServiceProvider',
            'Illuminate\Cookie\CookieServiceProvider',
            'Illuminate\Database\DatabaseServiceProvider',
            'Illuminate\Encryption\EncryptionServiceProvider',
            'Illuminate\Filesystem\FilesystemServiceProvider',
            'Illuminate\Foundation\Providers\FoundationServiceProvider',
            'Illuminate\Hashing\HashServiceProvider',
            'Illuminate\Mail\MailServiceProvider',
            'Illuminate\Notifications\NotificationServiceProvider',
            'Illuminate\Pagination\PaginationServiceProvider',
            'Illuminate\Pipeline\PipelineServiceProvider',
            'Illuminate\Queue\QueueServiceProvider',
            'Illuminate\Redis\RedisServiceProvider',
            'Illuminate\Auth\Passwords\PasswordResetServiceProvider',
            'Illuminate\Session\SessionServiceProvider',
            'Illuminate\Translation\TranslationServiceProvider',
            'Illuminate\Validation\ValidationServiceProvider',
            'Illuminate\View\ViewServiceProvider',
        ])->filter(function ($provider) {
            return class_exists($provider);
        })->each(function ($provider) {
            $this->app->register($provider);
        });
    }

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
        $this->app['config']['database.connections'] = $connections;
        $this->app['config']['database.default'] = $default;
        $this->app['config']['database.fetch'] = $fetch;

        return $this->bootProvider(DatabaseServiceProvider::class);
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
        if (! isset($this->app['path.lang'])) {
            // default pagination view without translation
            $this->app['view']->addNamespace('pagination', __DIR__.'/../../resources/views/pagination/');
        }

        return $this->bootProvider(PaginationServiceProvider::class);
    }

    /**
     * @param string $langPath
     *
     * @return static
     */
    public function setupTranslator($langPath)
    {
        $this->app->instance('path.lang', $langPath);

        return $this->bootProvider(TranslationServiceProvider::class);
    }

    /**
     * @param string|array $viewPath
     * @param string $compiledPath
     *
     * @return static
     */
    public function setupView($viewPath, $compiledPath)
    {
        $this->app['config']['view.paths'] = is_array($viewPath) ? $viewPath : [$viewPath];
        $this->app['config']['view.compiled'] = $compiledPath;

        return $this->bootProvider(ViewServiceProvider::class);
    }

    /**
     * @param string|mixed $provider
     * @return static
     */
    public function bootProvider($provider)
    {
        $provider = $this->app->getProvider($provider);

        if (method_exists($provider, 'boot')) {
            $this->app->call([$provider, 'boot']);
        }

        return $this;
    }
}
