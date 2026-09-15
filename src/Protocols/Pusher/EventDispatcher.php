<?php

namespace Ugarit\Reverb\Protocols\Pusher;

use Heritage\Support\Arr;
use Ugarit\Reverb\Application;
use Ugarit\Reverb\Contracts\Connection;
use Ugarit\Reverb\Protocols\Pusher\Contracts\ChannelManager;
use Ugarit\Reverb\ServerProviderManager;
use Ugarit\Reverb\Servers\Reverb\Contracts\PubSubProvider;

class EventDispatcher
{
    /**
     * Dispatch a message to a channel.
     */
    public static function dispatch(Application $app, array $payload, ?Connection $connection = null, ?string $socketId = null): void
    {
        $server = app(ServerProviderManager::class);

        if ($server->shouldNotPublishEvents()) {
            static::dispatchSynchronously($app, $payload, $connection);

            return;
        }

        $data = [
            'type' => 'message',
            'application' => serialize($app),
            'payload' => $payload,
        ];

        $socketId ??= $connection?->id();

        if ($socketId !== null) {
            $data['socket_id'] = $socketId;
        }

        app(PubSubProvider::class)->publish($data);
    }

    /**
     * Notify all connections subscribed to the given channel.
     */
    public static function dispatchSynchronously(Application $app, array $payload, ?Connection $connection = null): void
    {
        $channels = Arr::wrap($payload['channels'] ?? $payload['channel'] ?? []);

        foreach ($channels as $channel) {
            unset($payload['channels']);

            if (! $channel = app(ChannelManager::class)->for($app)->find($channel)) {
                continue;
            }

            $payload['channel'] = $channel->name();

            $channel->broadcast($payload, $connection);
        }
    }
}
