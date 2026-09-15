<?php

namespace Ugarit\Reverb\Protocols\Pusher\Channels;

use Ugarit\Reverb\Protocols\Pusher\Channels\Concerns\InteractsWithPrivateChannels;

class PrivateChannel extends Channel
{
    use InteractsWithPrivateChannels;
}
