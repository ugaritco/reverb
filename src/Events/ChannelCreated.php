<?php

namespace Ugarit\Reverb\Events;

use Heritage\Foundation\Events\Dispatchable;
use Ugarit\Reverb\Protocols\Pusher\Channels\Channel;

class ChannelCreated
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(public Channel $channel)
    {
        //
    }
}
