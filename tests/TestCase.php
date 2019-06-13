<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Recca0120\LaravelBridge\Laravel;

class TestCase extends BaseTestCase
{
    public function tearDown()
    {
        Mockery::close();

        Laravel::flashInstance();
    }

    protected function resourcePath($path = '')
    {
        $defaultPath = __DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'resources';

        return $defaultPath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    protected function storagePath($path = '')
    {
        $defaultPath = __DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'storage';

        return $defaultPath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}
