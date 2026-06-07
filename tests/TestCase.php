<?php

declare(strict_types=1);

namespace Html2img\Laravel\Tests;

use Html2img\Laravel\Html2imgServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            Html2imgServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('html2img.api_key', 'test-key');
    }
}
