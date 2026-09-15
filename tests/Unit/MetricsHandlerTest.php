<?php

use Ugarit\Reverb\Contracts\ApplicationProvider;
use Ugarit\Reverb\Protocols\Pusher\Contracts\ChannelManager;
use Ugarit\Reverb\Protocols\Pusher\MetricsHandler;
use Ugarit\Reverb\Protocols\Pusher\MetricType;
use Ugarit\Reverb\Protocols\Pusher\PendingMetric;
use Ugarit\Reverb\ServerProviderManager;
use Ugarit\Reverb\Servers\Reverb\Contracts\PubSubProvider;
use React\EventLoop\Loop;
use React\Promise\Deferred;

afterEach(function () {
    $loop = (new ReflectionClass(Loop::class));
    $property = $loop->getProperty('instance');

    if (PHP_VERSION_ID < 80100) {
        $property->setAccessible(true);
    }

    $property->setValue($loop, null);
});

/**
 * @see https://github.com/ugarit/reverb/issues/331
 */
it('removes the listener after metrics are gathered successfully', function () {
    $app = app(ApplicationProvider::class)->findByKey('reverb-key');
    app(ServerProviderManager::class)->withPublishing();
    $metric = new PendingMetric('test', $app, MetricType::CONNECTIONS);

    $stopListeningCalled = false;
    $stopListeningKey = null;
    $registeredEvent = null;

    $pubSub = Mockery::mock(PubSubProvider::class);

    $pubSub->shouldReceive('on')
        ->once()
        ->with(Mockery::on(fn ($event) => str_starts_with($event, 'test')), Mockery::type('callable'))
        ->andReturnUsing(function ($event, $listener) use (&$registeredListener, &$registeredEvent) {
            $registeredListener = $listener;
            $registeredEvent = $event;
        });

    $pubSub->shouldReceive('publish')
        ->once()
        ->andReturnUsing(function ($payload) use (&$registeredListener) {

            Loop::addTimer(0.001, function () use (&$registeredListener) {
                if ($registeredListener) {
                    $registeredListener([
                        'key' => 'test',
                        'payload' => ['connections' => []],
                    ]);
                }
            });

            $deferred = new Deferred;
            $deferred->resolve(1);

            return $deferred->promise();
        });

    $pubSub->shouldReceive('stopListening')
        ->with(Mockery::on(function ($key) use (&$stopListeningCalled, &$stopListeningKey, &$registeredEvent) {
            $stopListeningCalled = true;
            $stopListeningKey = $key;

            return $registeredEvent === $key;
        }));

    $this->app->instance(PubSubProvider::class, $pubSub);

    $handler = new MetricsHandler(
        app(ServerProviderManager::class),
        app(ChannelManager::class),
        $pubSub
    );

    $reflection = new ReflectionClass($handler);
    $gatherMethod = $reflection->getMethod('gatherMetricsFromSubscribers');
    $gatherMethod->setAccessible(true);

    $gatherMethod->invoke($handler, $metric);

    expect($reflection->getProperty('metrics')->getValue($handler))->toHaveKey('test');

    Loop::addTimer(0.1, fn () => Loop::stop());
    Loop::run();

    expect($stopListeningCalled)->toBeTrue();
    expect($registeredEvent)->toBe($stopListeningKey);
    expect($reflection->getProperty('metrics')->getValue($handler))->not->toHaveKey('test');
});
