<?php

namespace Ugarit\Reverb\Tests;

use Heritage\Foundation\Application;
use Heritage\Support\ServiceProvider;
use Ugarit\Reverb\ApplicationManagerServiceProvider;
use Ugarit\Reverb\Contracts\Logger;
use Ugarit\Reverb\Loggers\NullLogger;
use Ugarit\Reverb\Protocols\Pusher\Contracts\ChannelConnectionManager;
use Ugarit\Reverb\Protocols\Pusher\Contracts\ChannelManager;
use Ugarit\Reverb\Protocols\Pusher\Managers\ArrayChannelConnectionManager;
use Ugarit\Reverb\Protocols\Pusher\Managers\ArrayChannelManager;
use Ugarit\Reverb\ReverbServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class TestCase extends TestbenchTestCase
{
    /**
     * Setup the test environment.
     */
    protected function defineEnvironment($app): void
    {
        $app->instance(Logger::class, new NullLogger);

        $app->singleton(
            ChannelManager::class,
            fn () => new ArrayChannelManager
        );

        $app->bind(
            ChannelConnectionManager::class,
            fn () => new ArrayChannelConnectionManager
        );
    }

    /**
     * Get package providers.
     *
     * @param  Application  $app
     * @return array<int, class-string<ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            ReverbServiceProvider::class,
            ApplicationManagerServiceProvider::class,
        ];
    }
}
