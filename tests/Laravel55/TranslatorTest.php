<?php

namespace Tests\Laravel55;

use Illuminate\Support\Facades\View;
use Recca0120\LaravelBridge\Laravel;
use Tests\TestCase;

class TranslatorTest extends TestCase
{
    public function testLangDirective()
    {
        Laravel::getInstance()
            ->setupLocale('en')
            ->setupTranslator($this->resourcePath('lang'))
            ->setupView($this->resourcePath('views'), $this->storagePath('framework/views'));

        $actual = View::make('lang_test')->render();

        $this->assertSame('bar', $actual);
    }
}
