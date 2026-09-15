<?php

namespace Ugarit\Reverb\Contracts;

use Heritage\Support\Collection;
use Ugarit\Reverb\Application;
use Ugarit\Reverb\Exceptions\InvalidApplication;

interface ApplicationProvider
{
    /**
     * Get all of the configured applications as Application instances.
     *
     * @return Collection<Application>
     */
    public function all(): Collection;

    /**
     * Find an application instance by ID.
     *
     * @throws InvalidApplication
     */
    public function findById(string $id): Application;

    /**
     * Find an application instance by key.
     *
     * @throws InvalidApplication
     */
    public function findByKey(string $key): Application;
}
