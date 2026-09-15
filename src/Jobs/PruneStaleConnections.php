<?php

namespace Ugarit\Reverb\Jobs;

use Heritage\Foundation\Bus\Dispatchable;
use Ugarit\Reverb\Contracts\ApplicationProvider;
use Ugarit\Reverb\Events\ConnectionPruned;
use Ugarit\Reverb\Loggers\Log;
use Ugarit\Reverb\Protocols\Pusher\Contracts\ChannelManager;

class PruneStaleConnections
{
    use Dispatchable;

    /**
     * Execute the job.
     */
    public function handle(ChannelManager $channels): void
    {
        Log::info('Pruning Stale Connections');

        app(ApplicationProvider::class)
            ->all()
            ->each(function ($application) use ($channels) {
                foreach ($channels->for($application)->connections() as $connection) {
                    if (! $connection->isStale()) {
                        continue;
                    }

                    $connection->send(json_encode([
                        'event' => 'pusher:error',
                        'data' => json_encode([
                            'code' => 4201,
                            'message' => 'Pong reply not received in time',
                        ]),
                    ]));

                    $channels
                        ->for($connection->app())
                        ->unsubscribeFromAll($connection->connection());

                    $connection->disconnect();

                    Log::info('Connection Pruned', $connection->id());

                    ConnectionPruned::dispatch($connection);
                }
            });
    }
}
