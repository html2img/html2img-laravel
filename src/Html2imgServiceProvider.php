<?php

declare(strict_types=1);

namespace Html2img\Laravel;

use GuzzleHttp\ClientInterface;
use Html2img\Html2imgClient;
use Html2img\Laravel\Console\TestCommand;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class Html2imgServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/html2img.php', 'html2img');

        $this->app->singleton(Html2imgClient::class, function ($app): Html2imgClient {
            /** @var Repository $config */
            $config = $app['config'];

            return new Html2imgClient(
                apiKey: (string) $config->get('html2img.api_key', ''),
                baseUri: (string) $config->get('html2img.base_uri', Html2imgClient::DEFAULT_BASE_URI),
                timeout: (float) $config->get('html2img.timeout', Html2imgClient::DEFAULT_TIMEOUT),
                httpClient: $this->resolveHttpClient($app),
            );
        });

        $this->app->singleton(Html2img::class, fn ($app): Html2img => new Html2img($app->make(Html2imgClient::class)));

        $this->app->alias(Html2img::class, 'html2img');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/html2img.php' => config_path('html2img.php'),
            ], 'html2img-config');

            $this->commands([TestCommand::class]);
        }
    }

    /**
     * Allow an application to supply its own Guzzle client (for retry
     * middleware, logging or testing) by binding `html2img.http`.
     */
    private function resolveHttpClient(Application $app): ?ClientInterface
    {
        if (! $app->bound('html2img.http')) {
            return null;
        }

        $client = $app->make('html2img.http');

        return $client instanceof ClientInterface ? $client : null;
    }
}
