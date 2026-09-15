<?php

use Heritage\Support\Collection;
use Ugarit\Reverb\Application;
use Ugarit\Reverb\Connection;
use Ugarit\Reverb\Contracts\ApplicationProvider;
use Ugarit\Reverb\Managers\Connections;
use Ugarit\Reverb\Protocols\Pusher\Channels\ChannelConnection;
use Ugarit\Reverb\Protocols\Pusher\Contracts\ChannelManager;
use Ugarit\Reverb\Tests\FakeConnection;
use Ugarit\Reverb\Tests\SerializableConnection;
use Ugarit\Reverb\Tests\TestCase;
use Ramsey\Uuid\Uuid;

uses(TestCase::class)->in(__DIR__.'/Unit');

/**
 * Create a defined number of connections.
 *
 * @return array<int, Connection|string>
 */
function factory(int $count = 1, array $data = [], bool $serializable = false): array
{
    return Collection::make(range(1, $count))->map(function () use ($data, $serializable) {
        return new ChannelConnection(
            $serializable
                ? new SerializableConnection(Uuid::uuid4())
                : new FakeConnection(Uuid::uuid4()),
            $data
        );
    })->all();
}

/**
 * Generate a valid Pusher authentication signature.
 */
function validAuth(string $connectionId, string $channel, ?string $data = null): string
{
    $signature = "{$connectionId}:{$channel}";

    if ($data) {
        $signature .= ":{$data}";
    }

    return 'app-key:'.hash_hmac('sha256', $signature, 'reverb-secret');
}

/**
 * Return the channel manager.
 */
function channels(?Application $app = null): ChannelManager
{
    return app(ChannelManager::class)
        ->for($app ?: app(ApplicationProvider::class)->all()->first());
}
