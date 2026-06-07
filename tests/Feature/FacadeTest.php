<?php

declare(strict_types=1);

use Html2img\Laravel\Facades\Html2img;
use Html2img\Request\HtmlRequest;
use Html2img\Request\ScreenshotRequest;
use Html2img\Response\RenderResponse;

it('renders html through the facade and sends the configured api key', function () {
    $history = [];
    fakeHttp([
        jsonResponse(200, [
            'success' => true,
            'id' => 'abc',
            'url' => 'https://i.html2img.com/abc.png',
            'credits_remaining' => 10,
        ]),
    ], $history);

    $response = Html2img::html(new HtmlRequest(html: '<h1>Hi</h1>', width: 1200, height: 630));

    $request = lastRequest($history);

    expect($response)->toBeInstanceOf(RenderResponse::class)
        ->and($response->url)->toBe('https://i.html2img.com/abc.png')
        ->and($response->creditsRemaining)->toBe(10)
        ->and($request->getMethod())->toBe('POST')
        ->and($request->getUri()->getPath())->toBe('/api/html')
        ->and($request->getHeaderLine('X-API-Key'))->toBe('test-key');
});

it('captures a screenshot through the facade', function () {
    $history = [];
    fakeHttp([
        jsonResponse(200, ['success' => true, 'id' => 'x', 'url' => 'https://i.html2img.com/x.png']),
    ], $history);

    Html2img::screenshot(new ScreenshotRequest(url: 'https://example.com', selector: '#hero'));

    $request = lastRequest($history);
    $body = json_decode((string) $request->getBody(), true);

    expect($request->getUri()->getPath())->toBe('/api/screenshot')
        ->and($body)->toBe(['url' => 'https://example.com', 'selector' => '#hero']);
});

it('renders a template through the facade', function () {
    $history = [];
    fakeHttp([
        jsonResponse(200, [
            'success' => true,
            'id' => 'x',
            'template' => 'invoice-image',
            'url' => 'https://i.html2img.com/x.png',
        ]),
    ], $history);

    $response = Html2img::template('invoice-image', ['number' => 1042]);

    $request = lastRequest($history);

    expect($request->getUri()->getPath())->toBe('/api/v1/templates/invoice-image')
        ->and($response->template)->toBe('invoice-image')
        ->and(json_decode((string) $request->getBody(), true))->toBe(['number' => 1042]);
});
