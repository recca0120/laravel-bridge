<?php

namespace Tests;

use Illuminate\View\ViewServiceProvider;
use Mockery as m;
use PHPUnit_Framework_TestCase;
use Recca0120\LaravelBridge\Laravel;

class LaravelTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testInstance()
    {
        Laravel::instance()
            ->setupView(__DIR__, __DIR__);
    }

    public function testSetupPagination()
    {
        Laravel::instance()
            ->setupRunningInConsole(false)
            ->setupPagination();
    }

    public function testSetupCustomProvider()
    {
        Laravel::instance()
            ->setupCustomProvider(function ($app) {
                $app['config']['view.paths'] = [__DIR__];
                $app['config']['view.compiled'] = __DIR__;

                return new ViewServiceProvider($app);
            });
    }
}
