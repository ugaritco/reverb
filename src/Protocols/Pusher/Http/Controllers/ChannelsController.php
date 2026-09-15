<?php

namespace Ugarit\Reverb\Protocols\Pusher\Http\Controllers;

use Ugarit\Reverb\Protocols\Pusher\MetricsHandler;
use Ugarit\Reverb\Servers\Reverb\Http\Connection;
use Ugarit\Reverb\Servers\Reverb\Http\Response;
use Psr\Http\Message\RequestInterface;
use React\Promise\PromiseInterface;

class ChannelsController extends Controller
{
    /**
     * Handle the request.
     */
    public function __invoke(RequestInterface $request, Connection $connection, string $appId): PromiseInterface
    {
        $this->verify($request, $connection, $appId);

        return app(MetricsHandler::class)->gather($this->application, 'channels', [
            'filter' => $this->query['filter_by_prefix'] ?? null,
            'info' => $this->query['info'] ?? null,
        ])->then(fn ($channels) => new Response(['channels' => array_map(fn ($item) => (object) $item, $channels)]));
    }
}
