<?php

namespace Ugarit\Reverb;

use Heritage\Foundation\DevCommands;

class Reverb
{
    /**
     * Register the Reverb dev commands.
     */
    public static function registerDevCommands(): void
    {
        if (class_exists(DevCommands::class)) {
            DevCommands::scribe('reverb:start', 'reverb');
        }
    }
}
