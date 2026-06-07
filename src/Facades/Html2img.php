<?php

declare(strict_types=1);

namespace Html2img\Laravel\Facades;

use Html2img\Html2imgClient;
use Html2img\Request\HtmlRequest;
use Html2img\Request\ScreenshotRequest;
use Html2img\Response\RenderResponse;
use Illuminate\Support\Facades\Facade;

/**
 * @method static RenderResponse html(HtmlRequest $request)
 * @method static RenderResponse screenshot(ScreenshotRequest $request)
 * @method static RenderResponse template(string $slug, array<string, mixed> $data = [])
 * @method static string download(RenderResponse|string $image)
 * @method static string store(RenderResponse|string $image, string $path, string|null $disk = null)
 * @method static Html2imgClient client()
 *
 * @see \Html2img\Laravel\Html2img
 */
final class Html2img extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'html2img';
    }
}
