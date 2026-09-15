<?php

namespace Ugarit\Reverb\Servers\Reverb;

use InvalidArgumentException;
use Ugarit\Reverb\Certificate;
use Ugarit\Reverb\Contracts\ApplicationProvider;
use Ugarit\Reverb\Protocols\Pusher\Contracts\ChannelConnectionManager;
use Ugarit\Reverb\Protocols\Pusher\Contracts\ChannelManager;
use Ugarit\Reverb\Protocols\Pusher\Http\Controllers\ChannelController;
use Ugarit\Reverb\Protocols\Pusher\Http\Controllers\ChannelsController;
use Ugarit\Reverb\Protocols\Pusher\Http\Controllers\ChannelUsersController;
use Ugarit\Reverb\Protocols\Pusher\Http\Controllers\ConnectionsController;
use Ugarit\Reverb\Protocols\Pusher\Http\Controllers\EventsBatchController;
use Ugarit\Reverb\Protocols\Pusher\Http\Controllers\EventsController;
use Ugarit\Reverb\Protocols\Pusher\Http\Controllers\HealthCheckController;
use Ugarit\Reverb\Protocols\Pusher\Http\Controllers\PusherController;
use Ugarit\Reverb\Protocols\Pusher\Http\Controllers\UsersTerminateController;
use Ugarit\Reverb\Protocols\Pusher\Managers\ArrayChannelConnectionManager;
use Ugarit\Reverb\Protocols\Pusher\Managers\ArrayChannelManager;
use Ugarit\Reverb\Protocols\Pusher\PusherPubSubIncomingMessageHandler;
use Ugarit\Reverb\Protocols\Pusher\Server as PusherServer;
use Ugarit\Reverb\Servers\Reverb\Contracts\PubSubIncomingMessageHandler;
use Ugarit\Reverb\Servers\Reverb\Http\Route;
use Ugarit\Reverb\Servers\Reverb\Http\Router;
use Ugarit\Reverb\Servers\Reverb\Http\Server as HttpServer;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Socket\SocketServer;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class Factory
{
    /**
     * Create a new WebSocket server instance.
     */
    public static function make(
        string $host = '0.0.0.0',
        string $port = '8080',
        string $path = '',
        ?string $hostname = null,
        int $maxRequestSize = 10_000,
        array $options = [],
        string $protocol = 'pusher',
        ?LoopInterface $loop = null
    ): HttpServer {
        $loop = $loop ?: Loop::get();

        $router = match ($protocol) {
            'pusher' => static::makePusherRouter($path),
            default => throw new InvalidArgumentException("Unsupported protocol [{$protocol}]."),
        };

        $options['tls'] = static::configureTls($options['tls'] ?? [], $hostname);

        $uri = static::usesTls($options['tls']) ? "tls://{$host}:{$port}" : "{$host}:{$port}";

        return new HttpServer(
            new SocketServer($uri, $options, $loop),
            $router,
            $maxRequestSize,
            $loop
        );
    }

    /**
     * Create a new WebSocket server for the Pusher protocol.
     */
    public static function makePusherRouter(string $path): Router
    {
        app()->singletonIf(
            ChannelManager::class,
            fn () => new ArrayChannelManager
        );

        app()->bindIf(
            ChannelConnectionManager::class,
            fn () => new ArrayChannelConnectionManager
        );

        app()->singletonIf(
            PubSubIncomingMessageHandler::class,
            fn () => new PusherPubSubIncomingMessageHandler,
        );

        return new Router(new UrlMatcher(static::pusherRoutes($path), new RequestContext));
    }

    /**
     * Generate the routes required to handle Pusher requests.
     */
    protected static function pusherRoutes(string $path): RouteCollection
    {
        $routes = new RouteCollection;

        $routes->add('sockets', Route::get('/app/{appKey}', new PusherController(app(PusherServer::class), app(ApplicationProvider::class))));
        $routes->add('events', Route::post('/apps/{appId}/events', new EventsController));
        $routes->add('events_batch', Route::post('/apps/{appId}/batch_events', new EventsBatchController));
        $routes->add('connections', Route::get('/apps/{appId}/connections', new ConnectionsController));
        $routes->add('channels', Route::get('/apps/{appId}/channels', new ChannelsController));
        $routes->add('channel', Route::get('/apps/{appId}/channels/{channel}', new ChannelController));
        $routes->add('channel_users', Route::get('/apps/{appId}/channels/{channel}/users', new ChannelUsersController));
        $routes->add('users_terminate', Route::post('/apps/{appId}/users/{userId}/terminate_connections', new UsersTerminateController));
        $routes->add('health_check', Route::get('/up', new HealthCheckController));

        $routes->addPrefix($path);

        return $routes;
    }

    /**
     * Configure the TLS context for the server.
     *
     * @param  array  $context<string,  mixed>
     * @return array<string, mixed>
     */
    protected static function configureTls(array $context, ?string $hostname): array
    {
        $context = array_filter($context, fn ($value) => $value !== null);

        if (! static::usesTls($context) && $hostname && Certificate::exists($hostname)) {
            [$certificate, $key] = Certificate::resolve($hostname);

            $context['local_cert'] = $certificate;
            $context['local_pk'] = $key;
            $context['verify_peer'] = app()->environment() === 'production';
        }

        return $context;
    }

    /**
     * Determine whether the server uses TLS.
     *
     * @param  array  $context<string,  mixed>
     */
    protected static function usesTls(array $context): bool
    {
        return ($context['local_cert'] ?? false) || ($context['local_pk'] ?? false);
    }
}
