<?php

namespace ChristianoErick\Base;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

use ChristianoErick\Base\Commands\{InstallCommand, SeedContentCommand};

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../stubs/config/admin.php', 'admin'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Registrar comandos
            $this->commands([
                InstallCommand::class,
                SeedContentCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../stubs/.env.2i9' => base_path('.env.2i9'),
                __DIR__.'/../stubs/config/2i9_import.php' => config_path('2i9_import.php'),
                __DIR__.'/../stubs/App' => app_path(),
            ], 'admin-config');
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
