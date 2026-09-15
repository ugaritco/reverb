<?php

namespace Ugarit\Reverb\Protocols\Pusher\Http\Controllers;

use Ugarit\Reverb\Servers\Reverb\Http\Connection;
use Ugarit\Reverb\Servers\Reverb\Http\Response;
use Psr\Http\Message\RequestInterface;

class HealthCheckController extends Controller
{
    /**
     * Handle the request.
     */
    public function __invoke(RequestInterface $request, Connection $connection): Response
    {
        return new Response((object) ['health' => 'OK']);
    }
}
