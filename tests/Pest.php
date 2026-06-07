<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Html2img\Html2imgClient;
use Html2img\Laravel\Html2img;
use Html2img\Laravel\Tests\TestCase;
use Psr\Http\Message\RequestInterface;

uses(TestCase::class)->in(__DIR__);

/**
 * Bind a mocked Guzzle client into the container as `html2img.http` so the
 * SDK makes no real network call, and rebuild the bindings that depend on it.
 * The $history array is filled, by reference, with the outgoing requests.
 *
 * @param  list<Response|Throwable>  $queue
 * @param  array<int, mixed>  $history
 */
function fakeHttp(array $queue, array &$history = []): void
{
    $mock = new MockHandler($queue);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $guzzle = new Client([
        'handler' => $stack,
        'base_uri' => Html2imgClient::DEFAULT_BASE_URI,
    ]);

    app()->instance('html2img.http', $guzzle);

    foreach ([Html2imgClient::class, Html2img::class, 'html2img'] as $abstract) {
        app()->forgetInstance($abstract);
    }
}

/**
 * A JSON response with the given status and body.
 *
 * @param  array<string, mixed>  $body
 */
function jsonResponse(int $status, array $body): Response
{
    return new Response($status, ['Content-Type' => 'application/json'], json_encode($body, JSON_THROW_ON_ERROR));
}

/**
 * The most recently recorded outgoing request.
 *
 * @param  array<int, mixed>  $history
 */
function lastRequest(array $history): RequestInterface
{
    $last = end($history);
    $request = is_array($last) ? ($last['request'] ?? null) : null;

    if (! $request instanceof RequestInterface) {
        throw new RuntimeException('No request was recorded.');
    }

    return $request;
}
