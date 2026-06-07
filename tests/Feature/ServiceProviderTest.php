<?php

declare(strict_types=1);

use Html2img\Html2imgClient;
use Html2img\Laravel\Html2img;

it('merges the default config', function () {
    expect(config('html2img.base_uri'))->toBe('https://app.html2img.com')
        ->and(config('html2img.timeout'))->toBe(35);
});

it('binds the underlying SDK client as a singleton', function () {
    expect(app(Html2imgClient::class))->toBeInstanceOf(Html2imgClient::class)
        ->and(app(Html2imgClient::class))->toBe(app(Html2imgClient::class));
});

it('binds the manager under its class and the html2img alias', function () {
    expect(app(Html2img::class))->toBeInstanceOf(Html2img::class)
        ->and(app('html2img'))->toBe(app(Html2img::class));
});

it('exposes the underlying client through the manager', function () {
    expect(app(Html2img::class)->client())->toBeInstanceOf(Html2imgClient::class);
});
