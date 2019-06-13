<?php

namespace Tests\Laravel52;

use Illuminate\Support\Collection;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\View\ViewServiceProvider;
use Recca0120\LaravelBridge\Laravel;
use Tests\TestCase;

class LaravelTest extends TestCase
{
    public function testGetInstanceInContainer()
    {
        $instance = Laravel::getInstance();

        $this->assertInstanceOf(Collection::class, $instance->get(Collection::class));
    }

    /**
     * @expectedException \Psr\Container\NotFoundExceptionInterface
     */
    public function testShouldThrowExceptionWhenGetNotExistClass()
    {
        $instance = Laravel::getInstance();

        $instance->get('whatever');
    }

    public function testCheckInstanceInContainer()
    {
        $instance = Laravel::getInstance()
            ->setupView(__DIR__, __DIR__);

        $this->assertTrue($instance->has('view'));
        $this->assertFalse($instance->has('whatever'));
    }

    public function testInstance()
    {
        $actual = Laravel::getInstance()
            ->setupView(__DIR__, __DIR__);

        $this->assertInstanceOf(ViewFactory::class, $actual->make('view'));
    }

    public function testSetupPagination()
    {
        $actual = Laravel::getInstance()
            ->setupRunningInConsole(false)
            ->setupView(__DIR__, __DIR__)
            ->setupPagination();

        $this->assertInstanceOf(ViewFactory::class, $actual->make('view'));
    }

    public function testSetupCallableProvider()
    {
        $actual = Laravel::getInstance()
            ->setupCallableProvider(function ($app) {
                $app['config']['view.paths'] = [__DIR__];
                $app['config']['view.compiled'] = __DIR__;

                return new ViewServiceProvider($app);
            });

        $this->assertInstanceOf(ViewFactory::class, $actual->make('view'));
    }
}
