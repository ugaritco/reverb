<?php

namespace Ugarit\Reverb\Jobs;

use Heritage\Foundation\Bus\Dispatchable;
use Ugarit\Reverb\Contracts\ApplicationProvider;
use Ugarit\Reverb\Loggers\Log;
use Ugarit\Reverb\Protocols\Pusher\Contracts\ChannelManager;
use Ugarit\Reverb\Protocols\Pusher\EventHandler;

class PingInactiveConnections
{
    use Dispatchable;

    /**
     * Execute the job.
     */
    public function handle(ChannelManager $channels): void
    {
        Log::info('Pinging Inactive Connections');

        $pusher = new EventHandler($channels);

        app(ApplicationProvider::class)
            ->all()
            ->each(function ($application) use ($channels, $pusher) {
                foreach ($channels->for($application)->connections() as $connection) {
                    if ($connection->isActive()) {
                        continue;
                    }

                    $pusher->ping($connection->connection());

                    Log::info('Connection Pinged', $connection->id());
                }
            });
    }
}
