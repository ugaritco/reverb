<?php

namespace Ugarit\Reverb;

use Heritage\Contracts\Foundation\Application;
use Heritage\Support\Manager;
use Ugarit\Reverb\Servers\Reverb\ReverbServerProvider;

class ServerProviderManager extends Manager
{
    /**
     * Create a new server manager instance.
     */
    public function __construct(protected Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Creates the Reverb driver.
     */
    public function createReverbDriver(): ReverbServerProvider
    {
        return new ReverbServerProvider(
            $this->app,
            $this->config->get('reverb.servers.reverb', [])
        );
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('reverb.default', 'reverb');
    }
}
