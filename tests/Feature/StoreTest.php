<?php

declare(strict_types=1);

use Html2img\Laravel\Facades\Html2img;
use Html2img\Response\RenderResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('downloads the rendered image bytes from a url', function () {
    Http::fake(['i.html2img.com/*' => Http::response('PNG-BYTES')]);

    expect(Html2img::download('https://i.html2img.com/x.png'))->toBe('PNG-BYTES');
});

it('downloads from a render response', function () {
    Http::fake(['i.html2img.com/*' => Http::response('PNG-BYTES')]);

    $response = RenderResponse::fromArray([
        'success' => true,
        'id' => 'x',
        'url' => 'https://i.html2img.com/x.png',
    ]);

    expect(Html2img::download($response))->toBe('PNG-BYTES');
});

it('stores a render on a named disk and returns the path', function () {
    Storage::fake('images');
    Http::fake(['i.html2img.com/*' => Http::response('PNG-BYTES')]);

    $response = RenderResponse::fromArray([
        'success' => true,
        'id' => 'x',
        'url' => 'https://i.html2img.com/x.png',
    ]);

    $path = Html2img::store($response, 'cards/welcome.png', 'images');

    expect($path)->toBe('cards/welcome.png');
    Storage::disk('images')->assertExists('cards/welcome.png');
    expect(Storage::disk('images')->get('cards/welcome.png'))->toBe('PNG-BYTES');
});

it('uses the configured default disk when none is given', function () {
    config()->set('html2img.storage.disk', 'images');
    Storage::fake('images');
    Http::fake(['i.html2img.com/*' => Http::response('PNG-BYTES')]);

    Html2img::store('https://i.html2img.com/x.png', 'x.png');

    Storage::disk('images')->assertExists('x.png');
});

it('throws when storing an async render with no url yet', function () {
    $response = RenderResponse::fromArray([
        'success' => true,
        'id' => 'x',
        'status' => 'processing',
        'url' => null,
    ]);

    expect(fn () => Html2img::store($response, 'x.png'))->toThrow(RuntimeException::class);
});
