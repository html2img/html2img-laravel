<?php

declare(strict_types=1);

namespace Html2img\Laravel\Console;

use Html2img\Exception\Html2imgException;
use Html2img\Laravel\Html2img;
use Html2img\Request\HtmlRequest;
use Illuminate\Console\Command;

final class TestCommand extends Command
{
    protected $signature = 'html2img:test';

    protected $description = 'Render a small test image to verify your html2img configuration (uses one credit).';

    public function handle(Html2img $html2img): int
    {
        if (blank(config('html2img.api_key'))) {
            $this->error('No API key configured. Set HTML2IMG_API_KEY in your .env file.');

            return self::FAILURE;
        }

        $this->line('Rendering a test image via the html2img API...');

        try {
            $response = $html2img->html(new HtmlRequest(
                html: '<!doctype html><html><body style="font-family:system-ui;display:flex;align-items:center;justify-content:center;height:180px;margin:0;background:#0f172a;color:#fff"><h1>html2img is configured</h1></body></html>',
                width: 600,
                height: 200,
            ));
        } catch (Html2imgException $e) {
            $this->error('Request failed: '.$e->getMessage().($e->errorCode() !== null ? ' ('.$e->errorCode().')' : ''));

            return self::FAILURE;
        }

        $this->info('Test render succeeded.');
        $this->line('Image URL: '.(string) $response->url);

        if ($response->creditsRemaining !== null) {
            $this->line('Credits remaining: '.$response->creditsRemaining);
        }

        return self::SUCCESS;
    }
}
