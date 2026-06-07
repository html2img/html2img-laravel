<?php

declare(strict_types=1);

it('reports success from the html2img:test command', function () {
    fakeHttp([
        jsonResponse(200, [
            'success' => true,
            'id' => 'abc',
            'url' => 'https://i.html2img.com/abc.png',
            'credits_remaining' => 42,
        ]),
    ]);

    $this->artisan('html2img:test')
        ->expectsOutputToContain('Test render succeeded.')
        ->expectsOutputToContain('https://i.html2img.com/abc.png')
        ->assertSuccessful();
});

it('fails clearly when no api key is configured', function () {
    config()->set('html2img.api_key', null);

    $this->artisan('html2img:test')
        ->expectsOutputToContain('No API key configured')
        ->assertFailed();
});

it('maps an api error to a failure', function () {
    fakeHttp([
        jsonResponse(401, ['error' => 'Invalid API key', 'code' => 'invalid_api_key']),
    ]);

    $this->artisan('html2img:test')
        ->expectsOutputToContain('Invalid API key')
        ->assertFailed();
});
