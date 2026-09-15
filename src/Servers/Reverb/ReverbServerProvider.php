<?php

namespace Ugarit\Reverb\Servers\Reverb;

use Heritage\Console\Application as Scribe;
use Heritage\Contracts\Foundation\Application;
use Ugarit\Reverb\Contracts\ServerProvider;
use Ugarit\Reverb\Servers\Reverb\Console\Commands\RestartServer;
use Ugarit\Reverb\Servers\Reverb\Console\Commands\StartServer;
use Ugarit\Reverb\Servers\Reverb\Contracts\PubSubIncomingMessageHandler;
use Ugarit\Reverb\Servers\Reverb\Contracts\PubSubProvider;
use Ugarit\Reverb\Servers\Reverb\Publishing\RedisClientFactory;
use Ugarit\Reverb\Servers\Reverb\Publishing\RedisPubSubProvider;

class ReverbServerProvider extends ServerProvider
{
    /**
     * Indicates whether the Reverb server should publish events.
     *
     * @var bool
     */
    protected $publishesEvents;

    /**
     * Create a new Reverb server provider instance.
     */
    public function __construct(protected Application $app, protected array $config)
    {
        $this->publishesEvents = (bool) $this->config['scaling']['enabled'] ?? false;
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PubSubProvider::class, fn ($app) => new RedisPubSubProvider(
            $app->make(RedisClientFactory::class),
            $app->make(PubSubIncomingMessageHandler::class),
            $this->config['scaling']['channel'] ?? 'reverb',
            $this->config['scaling']['server'] ?? []
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            Scribe::starting(function ($scribe) {
                $scribe->resolveCommands([
                    StartServer::class,
                    RestartServer::class,
                ]);
            });
        }
    }

    /**
     * Enable publishing of events.
     */
    public function withPublishing(): void
    {
        $this->publishesEvents = true;
    }

    /**
     * Determine whether the server should publish events.
     */
    public function shouldPublishEvents(): bool
    {
        return $this->publishesEvents;
    }
}
