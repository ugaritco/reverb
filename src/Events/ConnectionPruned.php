<?php

namespace Ugarit\Reverb\Events;

use Heritage\Foundation\Events\Dispatchable;
use Ugarit\Reverb\Protocols\Pusher\Channels\ChannelConnection;

class ConnectionPruned
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(public ChannelConnection $connection)
    {
        //
    }
}
