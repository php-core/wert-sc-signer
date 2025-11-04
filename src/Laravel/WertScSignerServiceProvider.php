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

        // Register the credential manager as a singleton
        $this->app->singleton('wert-sc-signer.credentials', function ($app) {
            $config = $app['config']['wert-sc-signer'];

            return new CredentialManager(
                $config['credentials'] ?? [],
                $config['default_credential'] ?? 'default'
            );
        });

        // Register the main service as a singleton
        $this->app->singleton('wert-sc-signer', function ($app) {
            return new WertScSigner($app['wert-sc-signer.credentials']);
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