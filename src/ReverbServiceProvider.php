<?php

namespace Ugarit\Reverb;

use Heritage\Support\ServiceProvider;
use Ugarit\Pulse\Pulse;
use Ugarit\Reverb\Console\Commands\InstallCommand;
use Ugarit\Reverb\Contracts\Logger;
use Ugarit\Reverb\Loggers\NullLogger;
use Ugarit\Reverb\Pulse\Livewire;
use Livewire\LivewireManager;

class ReverbServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/reverb.php', 'reverb'
        );

        $this->app->instance(Logger::class, new NullLogger);

        $this->app->singleton(ServerProviderManager::class);

        $this->app->make(ServerProviderManager::class)->register();

        Reverb::registerDevCommands();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(InstallCommand::class);

            $this->publishes([
                __DIR__.'/../config/reverb.php' => config_path('reverb.php'),
            ], ['reverb', 'reverb-config']);

            if (method_exists($this, 'reloads')) {
                $this->reloads('reverb:restart', 'reverb');
            }
        }

        if ($this->app->bound(Pulse::class)) {
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'reverb');

            $this->callAfterResolving('livewire', function (LivewireManager $livewire) {
                $livewire->component('reverb.messages', Livewire\Messages::class);
                $livewire->component('reverb.connections', Livewire\Connections::class);
            });
        }

        $this->app->make(ServerProviderManager::class)->boot();
    }
}
