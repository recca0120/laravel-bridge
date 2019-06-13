<?php

namespace Tests;

use Illuminate\View\Factory as ViewFactory;
use Illuminate\View\ViewServiceProvider;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelBridge\Laravel;

class LaravelTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testInstance()
    {
        $actual = Laravel::createInstance()
            ->setupView(__DIR__, __DIR__);

        $this->assertInstanceOf(ViewFactory::class, $actual->make('view'));
    }

    public function testSetupPagination()
    {
        $actual = Laravel::createInstance()
            ->setupRunningInConsole(false)
            ->setupPagination();

        $this->assertInstanceOf(ViewFactory::class, $actual->make('view'));
    }

    public function testSetupCallableProvider()
    {
        $actual = Laravel::createInstance()
            ->setupCallableProvider(function ($app) {
                $app['config']['view.paths'] = [__DIR__];
                $app['config']['view.compiled'] = __DIR__;

                return new ViewServiceProvider($app);
            });

        $this->assertInstanceOf(ViewFactory::class, $actual->make('view'));
    }
}
