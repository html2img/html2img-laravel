<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | API key
    |--------------------------------------------------------------------------
    |
    | Your html2img API key, sent as the X-API-Key header on every request.
    | Issue one from your dashboard at https://app.html2img.com and keep it in
    | your environment, never in version control.
    |
    */

    'api_key' => env('HTML2IMG_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URI
    |--------------------------------------------------------------------------
    |
    | The base URI of the API. You should not need to change this; it exists
    | for testing and private deployments.
    |
    */

    'base_uri' => env('HTML2IMG_BASE_URI', 'https://app.html2img.com'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Request timeout in seconds. The default sits just over the 30 second
    | synchronous render budget. For captures likely to exceed it, pass a
    | webhook URL on the request instead of raising this.
    |
    */

    'timeout' => env('HTML2IMG_TIMEOUT', 35),

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | The default filesystem disk used by Html2img::store(). Leave null to use
    | your application's default disk.
    |
    */

    'storage' => [
        'disk' => env('HTML2IMG_DISK'),
    ],

];
