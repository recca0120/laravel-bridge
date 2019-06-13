<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
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
