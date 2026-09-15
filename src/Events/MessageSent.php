<?php

namespace Ugarit\Reverb\Events;

use Heritage\Foundation\Events\Dispatchable;
use Ugarit\Reverb\Contracts\Connection;

class MessageSent
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(public Connection $connection, public string $message)
    {
        //
    }
}
