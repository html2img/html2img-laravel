<?php

declare(strict_types=1);

namespace Html2img\Laravel;

use Html2img\Html2imgClient;
use Html2img\Request\HtmlRequest;
use Html2img\Request\ScreenshotRequest;
use Html2img\Response\RenderResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Laravel-flavoured wrapper around the html2img PHP SDK.
 *
 * Delegates the three render endpoints to the underlying
 * {@see Html2imgClient}, and adds Laravel conveniences for saving a render to
 * a filesystem disk. Resolve it from the container, type-hint it, or reach it
 * through the {@see Facades\Html2img} facade.
 */
final class Html2img
{
    public function __construct(private readonly Html2imgClient $client) {}

    /**
     * Render an HTML document to an image.
     */
    public function html(HtmlRequest $request): RenderResponse
    {
        return $this->client->html($request);
    }

    /**
     * Capture a screenshot of a live URL.
     */
    public function screenshot(ScreenshotRequest $request): RenderResponse
    {
        return $this->client->screenshot($request);
    }

    /**
     * Render a named template from a JSON data payload.
     *
     * @param  array<string, mixed>  $data
     */
    public function template(string $slug, array $data = []): RenderResponse
    {
        return $this->client->template($slug, $data);
    }

    /**
     * Download the rendered image bytes from its CDN URL.
     *
     * Accepts a {@see RenderResponse} or a URL string.
     */
    public function download(RenderResponse|string $image): string
    {
        return Http::get($this->urlFor($image))->throw()->body();
    }

    /**
     * Download a render and store it on a filesystem disk.
     *
     * Returns the stored path. Pass null for the disk to use the configured
     * default (the `html2img.storage.disk` config value, or your application's
     * default disk).
     */
    public function store(RenderResponse|string $image, string $path, ?string $disk = null): string
    {
        Storage::disk($disk ?? $this->defaultDisk())->put($path, $this->download($image));

        return $path;
    }

    /**
     * The underlying SDK client, for anything not surfaced here.
     */
    public function client(): Html2imgClient
    {
        return $this->client;
    }

    /**
     * Resolve a usable image URL from a response or string.
     */
    private function urlFor(RenderResponse|string $image): string
    {
        if (is_string($image)) {
            return $image;
        }

        if ($image->url === null) {
            throw new RuntimeException('The render has no image URL yet. Async jobs deliver their URL to the configured webhook.');
        }

        return $image->url;
    }

    /**
     * The configured default storage disk, if any.
     */
    private function defaultDisk(): ?string
    {
        $disk = config('html2img.storage.disk');

        return is_string($disk) ? $disk : null;
    }
}
