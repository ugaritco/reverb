<?php

namespace Ugarit\Reverb\Tests;

use Heritage\Support\Collection;
use Ugarit\Reverb\Application;
use Ugarit\Reverb\Contracts\ApplicationProvider;
use Ugarit\Reverb\Exceptions\InvalidApplication;

class FakeApplicationProvider implements ApplicationProvider
{
    /**
     * The applications collection.
     *
     * @var Collection<Application>
     */
    protected $apps;

    /**
     * Create a new fake provider instance.
     */
    public function __construct()
    {
        $this->apps = collect([
            new Application('id', 'key', 'secret', 60, 30, ['*'], 10_000, options: [
                'host' => 'localhost',
                'port' => 443,
                'scheme' => 'https',
                'useTLS' => true,
            ]),
        ]);
    }

    /**
     * Get all of the configured applications as Application instances.
     *
     * @return Collection<Application>
     */
    public function all(): Collection
    {
        return $this->apps;
    }

    /**
     * Find an application instance by ID.
     *
     * @throws InvalidApplication
     */
    public function findById(string $id): Application
    {
        return $this->apps->first();
    }

    /**
     * Find an application instance by key.
     *
     * @throws InvalidApplication
     */
    public function findByKey(string $key): Application
    {
        return $this->apps->first();
    }
}
