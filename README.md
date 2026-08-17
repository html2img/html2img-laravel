[![html2img — HTML to image API, rendered in real Chrome](https://html2img.com/og-image.png)](https://html2img.com)

# html2img for Laravel

[![Packagist Version](https://img.shields.io/packagist/v/html2img/html2img-laravel)](https://packagist.org/packages/html2img/html2img-laravel)
[![PHP Version](https://img.shields.io/packagist/php-v/html2img/html2img-laravel)](https://packagist.org/packages/html2img/html2img-laravel)
[![Laravel Version](https://img.shields.io/badge/laravel-11.x%20%7C%2012.x-FF2D20)](https://laravel.com)
[![Total Downloads](https://img.shields.io/packagist/dt/html2img/html2img-laravel)](https://packagist.org/packages/html2img/html2img-laravel)
[![License](https://img.shields.io/packagist/l/html2img/html2img-laravel)](LICENSE)

The official [Laravel](https://laravel.com) integration for the [HTML to Image API](https://html2img.com). Turn HTML and CSS into images, capture screenshots of live URLs, and render named templates, all behind a clean facade with zero-config auto-discovery.

It wraps the framework-agnostic [html2img PHP SDK](https://packagist.org/packages/html2img/html2img-php) ([source](https://github.com/html2img/html2img-php)) and adds the Laravel pieces you would otherwise write yourself: a service provider, a published config file, a facade, container bindings, an artisan health check, and one-line saving of a render to any [filesystem disk](https://laravel.com/docs/filesystem).

Every render runs in real Chrome, so flexbox, grid, custom properties, web fonts and inline JavaScript behave exactly as they do in the browser. The full API reference lives in the [documentation](https://html2img.com/docs), with a Laravel-specific guide at [html2img.com/integrations/laravel](https://html2img.com/integrations/laravel/).

## Contents

- [What you can build](#what-you-can-build)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Render HTML](#render-html)
  - [Capture a screenshot](#capture-a-screenshot)
  - [Generate a PDF](#generate-a-pdf)
  - [Render a template](#render-a-template)
- [Saving renders to a disk](#saving-renders-to-a-disk)
- [Queues and jobs](#queues-and-jobs)
- [Render options](#render-options)
- [The response](#the-response)
- [Asynchronous delivery](#asynchronous-delivery)
- [Error handling](#error-handling)
- [Verifying your setup](#verifying-your-setup)
- [Other languages](#other-languages)
- [Links](#links)

## What you can build

- **Open Graph and social images**, generated per page or post. See the [Open Graph image template](https://html2img.com/templates/open-graph-image) and [Twitter/X post template](https://html2img.com/templates/twitter-post).
- **Business documents** such as [invoices](https://html2img.com/templates/invoice-image), [receipts](https://html2img.com/templates/receipt-image), [event tickets](https://html2img.com/templates/event-ticket) and [certificates](https://html2img.com/templates/certificate-of-completion), as PNGs or as PDFs through the [HTML to PDF API](https://html2img.com/html-to-pdf/).
- **Developer assets** such as [code screenshots](https://html2img.com/templates/code-screenshot) and [GitHub social previews](https://html2img.com/templates/github-social-preview).
- **URL screenshots**, full page or cropped to a single element, with CSS injection to hide cookie banners and chat widgets before capture.

Browse the [full template library](https://html2img.com/templates), or try the no-signup [browser tools](https://html2img.com/tools) to see the output before you write any code.

## Requirements

- PHP 8.3 or newer
- Laravel 11, 12 or 13
- A html2img API key, issued per account from your [dashboard](https://app.html2img.com/register)

## Installation

```bash
composer require html2img/html2img-laravel
```

The service provider and the `Html2img` facade are registered automatically through [package discovery](https://laravel.com/docs/packages#package-discovery). Add your API key to `.env`:

```dotenv
HTML2IMG_API_KEY=your-api-key
```

That is the whole setup. See the [authentication docs](https://html2img.com/docs/authentication) for issuing and rotating keys, and the [getting started guide](https://html2img.com/docs/getting-started) for a tour of the API.

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=html2img-config
```

## Configuration

The published `config/html2img.php` reads from your environment:

```php
return [
    'api_key'  => env('HTML2IMG_API_KEY'),
    'base_uri' => env('HTML2IMG_BASE_URI', 'https://app.html2img.com'),
    'timeout'  => env('HTML2IMG_TIMEOUT', 35),
    'storage'  => [
        'disk' => env('HTML2IMG_DISK'),
    ],
];
```

| Variable             | Default                      | Purpose                                              |
| -------------------- | ---------------------------- | ---------------------------------------------------- |
| `HTML2IMG_API_KEY`   | none                         | Your key, sent as the `X-API-Key` header.            |
| `HTML2IMG_BASE_URI`  | `https://app.html2img.com`   | API base URI. You rarely need to change this.        |
| `HTML2IMG_TIMEOUT`   | `35`                         | Request timeout in seconds.                          |
| `HTML2IMG_DISK`      | default disk                 | Disk used by `Html2img::store()`.                    |

### Custom HTTP client

The integration is built on Guzzle. To add your own retry middleware, logging or proxy settings, bind a configured `GuzzleHttp\ClientInterface` as `html2img.http`, for example in a service provider:

```php
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

$this->app->bind('html2img.http', fn () => new Client([
    'base_uri' => config('html2img.base_uri'),
    'timeout'  => config('html2img.timeout'),
    // your own handler stack, middleware, proxy settings, etc.
]));
```

The package still sends the `X-API-Key`, `Accept` and `Content-Type` headers on every request.

## Usage

Reach the API through the `Html2img` facade. Each method returns a readonly `Html2img\Response\RenderResponse`. The request objects come from the underlying SDK, so import them from the `Html2img\Request` namespace.

### Render HTML

`POST /api/html`. Send a complete HTML document and get back an image of the rendered result. Inline your CSS in a `<style>` block, or reference remote stylesheets and web fonts via `<link>` tags in the document head. See the [`html` parameter docs](https://html2img.com/docs/parameters/html).

```php
use Html2img\Laravel\Facades\Html2img;
use Html2img\Request\HtmlRequest;

$response = Html2img::html(new HtmlRequest(
    html: view('og.post', ['post' => $post])->render(),
    css: 'body { background: #0f172a; color: #fff; }', // injected after load
    width: 1200,
    height: 630,
    dpi: 2,          // retina
));

return $response->url; // https://i.html2img.com/abc123def456.png
```

Rendering a Blade [view](https://laravel.com/docs/views) into the image, as above, keeps your markup where the rest of your app lives.

### Capture a screenshot

`POST /api/screenshot`. Fetch a public URL in a real browser and capture it. Use `selector` to crop to a single element, and `css` to hide cookie banners or chat widgets before the capture. See the [`url` parameter docs](https://html2img.com/docs/parameters/url) and the [`selector` docs](https://html2img.com/docs/parameters/selector).

```php
use Html2img\Laravel\Facades\Html2img;
use Html2img\Request\ScreenshotRequest;

$response = Html2img::screenshot(new ScreenshotRequest(
    url: 'https://example.com',
    width: 1200,
    height: 630,
    selector: '#hero',
    css: '.cookie-banner, .intercom-launcher { display: none !important; }',
    dpi: 2,
));
```

### Generate a PDF

Set `format` to `Format::Pdf` on either request and the render comes back as an A4 portrait vector PDF instead of a PNG: text stays selectable and searchable, webfonts are embedded and long content paginates automatically. The API ignores `width`, `height`, `dpi`, `fullpage` and `selector` in PDF mode, and the response `url` points at a `.pdf` file. One credit, the same as an image. This is the [HTML to PDF API](https://html2img.com/html-to-pdf/); see the [`format` parameter docs](https://html2img.com/docs/parameters/format).

```php
use Html2img\Enum\Format;
use Html2img\Laravel\Facades\Html2img;
use Html2img\Request\HtmlRequest;

$response = Html2img::html(new HtmlRequest(
    html: view('invoices.show', ['invoice' => $invoice])->render(),
    format: Format::Pdf,
));

// Store it like any other render
$path = Html2img::store($response, "invoices/{$invoice->number}.pdf");
```

### Render a template

`POST /api/v1/templates/{slug}`. Render one of the built-in [templates](https://html2img.com/templates) from a JSON data payload. The data is validated server-side per template. Templates output PNG only; `format` is not available on template renders.

```php
use Html2img\Laravel\Facades\Html2img;

$response = Html2img::template('invoice-image', [
    'number'   => 1042,
    'amount'   => '$240.00',
    'due_date' => '2026-07-01',
]);

return $response->url;
```

## Saving renders to a disk

The API returns the CDN URL of the image rather than the raw bytes, so you can cache and re-serve it from your own infrastructure. When you would rather keep a copy, `store()` downloads the image and writes it to any [filesystem disk](https://laravel.com/docs/filesystem) in one line:

```php
use Html2img\Laravel\Facades\Html2img;
use Html2img\Request\HtmlRequest;

$response = Html2img::html(new HtmlRequest(html: $document, width: 1200, height: 630));

// Returns the stored path; uses the HTML2IMG_DISK disk, or your default disk.
$path = Html2img::store($response, "og/{$post->id}.png");

// Or target a specific disk.
Html2img::store($response, "og/{$post->id}.png", 's3');

$post->update(['og_image_path' => $path]);
```

You can also pass a URL string directly, or grab the raw bytes without storing them:

```php
$bytes = Html2img::download($response);          // or a URL string
$path  = Html2img::store('https://i.html2img.com/abc.png', 'thumb.png');
```

## Queues and jobs

Renders are a natural fit for a [queued job](https://laravel.com/docs/queues), especially full-page captures that take a few seconds. Resolve the facade or type-hint the manager:

```php
use Html2img\Laravel\Html2img;
use Html2img\Request\HtmlRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateOgImage implements ShouldQueue
{
    use Queueable;

    public function __construct(public Post $post) {}

    public function handle(Html2img $html2img): void
    {
        $response = $html2img->html(new HtmlRequest(
            html: view('og.post', ['post' => $this->post])->render(),
            width: 1200,
            height: 630,
        ));

        $this->post->update([
            'og_image_path' => $html2img->store($response, "og/{$this->post->id}.png"),
        ]);
    }
}
```

For very large captures, prefer [asynchronous delivery](#asynchronous-delivery) over a long-running job.

## Render options

Both `HtmlRequest` and `ScreenshotRequest` accept the following. Any option left null is omitted from the request, so the server applies its own default. The complete reference is in the [parameter docs](https://html2img.com/docs/parameters).

| Option             | Type      | Docs                                                                       |
| ------------------ | --------- | -------------------------------------------------------------------------- |
| `css`              | string    | [css](https://html2img.com/docs/parameters/css)                            |
| `width`            | int       | [dimensions](https://html2img.com/docs/parameters/dimensions) (1 to 5000)  |
| `height`           | int       | [dimensions](https://html2img.com/docs/parameters/dimensions) (ignored when `fullpage`) |
| `fullpage`         | bool      | [fullpage](https://html2img.com/docs/parameters/fullpage)                  |
| `dpi`              | int       | [dpi](https://html2img.com/docs/parameters/dpi) (1 to 4, use 2 for retina) |
| `webhookUrl`       | string    | [webhook-url](https://html2img.com/docs/parameters/webhook-url)            |
| `msDelay`          | int       | [ms_delay](https://html2img.com/docs/parameters/ms_delay) (1 to 5000)      |
| `waitForSelector`  | string    | [wait_for_selector](https://html2img.com/docs/parameters/wait_for_selector) |
| `format`           | `Format`  | [format](https://html2img.com/docs/parameters/format): `Format::Png` (default) or `Format::Pdf`. PDF output is A4 portrait and ignores the sizing options above. |

`ScreenshotRequest` also accepts [`selector`](https://html2img.com/docs/parameters/selector) to crop the capture to a single element. `HtmlRequest` does not, since you control the markup.

Custom fonts are loaded by referencing them with `<link>` tags in your HTML document head, or by linking a web font from your captured page.

## The response

Every method returns a readonly `Html2img\Response\RenderResponse`:

```php
$response->success;          // bool
$response->id;               // string|null, the render id
$response->url;              // string|null, the CDN URL of the image
$response->creditsRemaining; // int|null, credits left after this call
$response->status;           // string|null, "processing" for async jobs
$response->message;          // string|null
$response->template;         // string|null, the template slug, when applicable
$response->isProcessing();   // bool
$response->raw();            // array, the full decoded JSON payload
```

## Asynchronous delivery

Synchronous requests have a 30 second budget. For captures likely to exceed it, pass a `webhookUrl`. The API responds immediately with `status: "processing"` and `url: null`, then POSTs the final image URL to your endpoint once rendering finishes. See the [`webhook_url` docs](https://html2img.com/docs/parameters/webhook-url).

```php
use Html2img\Laravel\Facades\Html2img;
use Html2img\Request\ScreenshotRequest;

$response = Html2img::screenshot(new ScreenshotRequest(
    url: 'https://example.com/long-report',
    fullpage: true,
    webhookUrl: route('hooks.html2img'),
));

if ($response->isProcessing()) {
    // The final URL will arrive at your webhook, not on this response.
}
```

## Error handling

Every failure throws an `Html2img\Exception\Html2imgException`. Catch that single type to handle any error, or catch a specific subclass. No raw Guzzle exception escapes the package.

```php
use Html2img\Laravel\Facades\Html2img;
use Html2img\Request\HtmlRequest;
use Html2img\Exception\Html2imgException;
use Html2img\Exception\ValidationException;
use Html2img\Exception\InsufficientCreditsException;

try {
    $response = Html2img::html(new HtmlRequest(html: $document));
} catch (ValidationException $e) {
    // 400 or 422: inspect the per-field messages
    foreach ($e->details() as $field => $messages) {
        // ...
    }
} catch (InsufficientCreditsException $e) {
    // 402: out of credits
    $left = $e->creditsRemaining();
} catch (Html2imgException $e) {
    // anything else
    $e->statusCode(); // int|null
    $e->errorCode();  // string|null, the API "code" field
    $e->payload();    // array, the decoded body
}
```

| Exception                       | When                                                  |
| ------------------------------- | ----------------------------------------------------- |
| `AuthenticationException`       | 401, missing or invalid API key.                      |
| `InsufficientCreditsException`  | 402, no credits remaining.                            |
| `NotSubscribedException`        | 403, no active subscription.                          |
| `NotFoundException`             | 404, for example an unknown template slug.            |
| `ValidationException`           | 400 or 422, with `details()` per field.               |
| `RateLimitException`            | 429, rate or quota exceeded.                          |
| `TimeoutException`              | 504, the synchronous render budget was exceeded.      |
| `ServerException`               | 5xx, an unexpected renderer error.                    |
| `ConnectionException`           | the request never reached a response.                 |
| `Html2imgException`             | base type for all of the above.                       |

## Verifying your setup

Confirm your key and configuration with the bundled artisan command, which renders a small test image:

```bash
php artisan html2img:test
```

It prints the resulting image URL and your remaining credits, or a clear error if the key is missing or rejected. The check uses one credit. There is also a [testing guide](https://html2img.com/docs/testing) for the API itself.

## Other languages

Not on Laravel? The same API has worked guides for
[plain PHP](https://html2img.com/integrations/php/),
[Ruby and Rails](https://html2img.com/integrations/ruby/),
[Python](https://html2img.com/integrations/python/),
[JavaScript and Node.js](https://html2img.com/integrations/javascript/),
[React](https://html2img.com/integrations/javascript/#react-and-nextjs) and
[Vue](https://html2img.com/integrations/javascript/#vue-and-nuxt).

## Development

This package uses [ddev](https://ddev.com) for a containerised PHP environment. It is optional, and you can use vanilla PHP or whatever you use for local dev if you prefer.

```bash
ddev composer install
ddev exec vendor/bin/pest      # tests
ddev exec vendor/bin/phpstan analyse
ddev exec vendor/bin/pint --test
```

## Links

[HTML to Image API](https://html2img.com) · [Screenshot API](https://html2img.com/screenshot-api/) · [HTML to PDF API](https://html2img.com/html-to-pdf/) · [Documentation](https://html2img.com/docs) · [Laravel guide](https://html2img.com/integrations/laravel/) · [Templates](https://html2img.com/templates) · [Tools](https://html2img.com/tools) · [Features](https://html2img.com/features) · [Comparisons](https://html2img.com/compare) · [Articles](https://html2img.com/articles) · [Pricing](https://html2img.com/pricing) · [PHP SDK](https://github.com/html2img/html2img-php)

## Licence

MIT. See [LICENSE](LICENSE).
