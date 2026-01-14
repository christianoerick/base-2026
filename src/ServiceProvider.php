<?php

namespace ChristianoErick\Base;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use ChristianoErick\Base\Commands\InstallCommand;

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
            ]);

            // Publicar migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'admin-migrations');

            // Publicar configurações
            $this->publishes([
                __DIR__.'/../stubs/config/admin.php' => config_path('admin.php'),
            ], 'admin-config');

            /*
            // Publicar stubs/arquivos
            $this->publishes([
                __DIR__.'/../stubs/app' => app_path(),
            ], 'admin-models');
            /* */
        }

        // Carregar migrations automaticamente
        //$this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}