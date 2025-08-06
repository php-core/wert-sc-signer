<?php

declare(strict_types=1);

namespace PHPCore\WertScSigner\Laravel;

use Illuminate\Support\ServiceProvider;
use PHPCore\WertScSigner\WertScSigner;

class WertScSignerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/wert-sc-signer.php',
            'wert-sc-signer'
        );

        $this->app->singleton('wert-sc-signer', function ($app) {
            return new WertScSigner();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/wert-sc-signer.php' => config_path('wert-sc-signer.php'),
            ], 'config');
        }
    }
}