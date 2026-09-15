<?php

namespace Ugarit\Reverb\Protocols\Pusher\Channels;

use Ugarit\Reverb\Protocols\Pusher\Channels\Concerns\InteractsWithPresenceChannels;

class PresenceCacheChannel extends CacheChannel
{
    use InteractsWithPresenceChannels;
}
