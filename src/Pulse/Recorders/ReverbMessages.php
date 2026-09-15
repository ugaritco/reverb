<?php

namespace Ugarit\Reverb\Pulse\Recorders;

use Carbon\CarbonImmutable;
use Heritage\Config\Repository;
use Ugarit\Pulse\Pulse;
use Ugarit\Pulse\Recorders\Concerns\Sampling;
use Ugarit\Reverb\Events\MessageReceived;
use Ugarit\Reverb\Events\MessageSent;

class ReverbMessages
{
    use Sampling;

    /**
     * The events to listen for.
     *
     * @var list<class-string>
     */
    public array $listen = [
        MessageSent::class,
        MessageReceived::class,
    ];

    /**
     * Create a new recorder instance.
     */
    public function __construct(
        protected Pulse $pulse,
        protected Repository $config
    ) {
        //
    }

    /**
     * Record the message.
     */
    public function record(MessageSent|MessageReceived $event): void
    {
        if (! $this->shouldSample()) {
            return;
        }

        $this->pulse->record(
            type: 'reverb_message:'.match ($event::class) {
                MessageSent::class => 'sent',
                MessageReceived::class => 'received',
            },
            key: $event->connection->app()->id(),
            timestamp: CarbonImmutable::now()->getTimestamp(),
        )->onlyBuckets()->count();
    }
}
