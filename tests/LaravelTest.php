<?php

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
            ->setupPagination();
    }
}
