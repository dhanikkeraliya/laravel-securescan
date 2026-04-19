<?php

namespace DhanikKeraliya\SecurityScanner;

use Illuminate\Support\ServiceProvider;
use DhanikKeraliya\SecurityScanner\Console\SecurityScanCommand;

class SecurityScannerServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot(): void
    {
        // ✅ Merge config (important)
        $this->mergeConfigFrom(
            __DIR__ . '/../config/securescan.php',
            'securescan'
        );

        // ✅ Publish config
        $this->publishes([
            __DIR__ . '/../config/securescan.php' => config_path('securescan.php'),
        ], 'securescan-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \DhanikKeraliya\SecurityScanner\Console\SecurityScanCommand::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'securescan');
    }
}