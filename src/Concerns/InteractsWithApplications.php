<?php

namespace Ugarit\Reverb\Concerns;

use Ugarit\Reverb\Application;

trait InteractsWithApplications
{
    /**
     * Set the application the channel manager should be scoped to.
     */
    public function for(Application $application): self
    {
        $this->application = $application;

        return $this;
    }
}
