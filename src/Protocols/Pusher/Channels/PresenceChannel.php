<?php

namespace Ugarit\Reverb\Protocols\Pusher\Channels;

use Ugarit\Reverb\Protocols\Pusher\Channels\Concerns\InteractsWithPresenceChannels;

class PresenceChannel extends PrivateChannel
{
    use InteractsWithPresenceChannels;
}
