<?php

use Illuminate\View\ViewServiceProvider;
use Mockery as m;
use Recca0120\LaravelBridge\Laravel;

class LaravelTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_instance()
    {
        Laravel::instance()
            ->setupView(__DIR__, __DIR__);
    }

    public function test_setup_custom_provider()
    {
        Laravel::instance()
            ->setupCustomProvider(function ($app) {
                $app['config']['view.paths'] = [__DIR__];
                $app['config']['view.compiled'] = __DIR__;

                return new ViewServiceProvider($app);
            });
    }
}
