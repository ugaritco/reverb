<?php

namespace Ugarit\Reverb\Pulse\Recorders;

use Heritage\Broadcasting\BroadcastManager;
use Heritage\Contracts\Foundation\Application as Container;
use Ugarit\Pulse\Events\IsolatedBeat;
use Ugarit\Pulse\Pulse;
use Ugarit\Pulse\Recorders\Concerns\Sampling;
use Ugarit\Reverb\Application;
use Ugarit\Reverb\Contracts\ApplicationProvider;

class ReverbConnections
{
    use Sampling;

    /**
     * The events to listen for.
     *
     * @var class-string
     */
    public string $listen = IsolatedBeat::class;

    /**
     * Create a new recorder instance.
     */
    public function __construct(
        protected Pulse $pulse,
        protected BroadcastManager $broadcast,
        protected Container $app,
    ) {
        //
    }

    /**
     * Record the connection count.
     */
    public function record(IsolatedBeat $event): void
    {
        if ($event->time->second % 15 !== 0) {
            return;
        }

        $this->app->make(ApplicationProvider::class)->all()
            ->each(function (Application $app) use ($event) {
                $connections = $this->broadcast->pusher($app->toArray())
                    ->get('/connections')
                    ->connections;

                $this->pulse->record(
                    type: 'reverb_connections',
                    key: $app->id(),
                    value: $connections,
                    timestamp: $event->time->getTimestamp(),
                )->avg()->max()->onlyBuckets();
            });
    }
}
