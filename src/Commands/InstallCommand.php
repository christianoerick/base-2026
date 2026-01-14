<?php

namespace ChristianoErick\Base\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'admin:install {--force : Sobrescrever arquivos existentes}';
    protected $description = 'Instala o painel administrativo';

    public function handle()
    {
        $this->info('Instalando painel administrativo...');

        // 1. Publicar configurações
        $this->call('vendor:publish', [
            '--tag' => 'admin-config',
            '--force' => $this->option('force')
        ]);

        // 2. Publicar models
        $this->call('vendor:publish', [
            '--tag' => 'admin-models',
            '--force' => $this->option('force')
        ]);

        // 3. Publicar recursos Filament
        $this->call('vendor:publish', [
            '--tag' => 'admin-filament',
            '--force' => $this->option('force')
        ]);

        // 4. Publicar migrations
        $this->call('vendor:publish', [
            '--tag' => 'admin-migrations',
            '--force' => $this->option('force')
        ]);

        // 5. Copiar arquivos adicionais manualmente se necessário
        $this->copyCustomFiles();

        // 6. Executar migrations
        if ($this->confirm('Deseja executar as migrations agora?', true)) {
            $this->call('migrate');
        }

        $this->info('✅ Instalação concluída com sucesso!');
        $this->line('Execute: php artisan admin:install para reinstalar se necessário');
    }

    protected function copyCustomFiles()
    {
        $files = [
            __DIR__.'/../../stubs/app/Providers/AppServiceProvider.php'
            => app_path('Providers/AppServiceProvider.php'),
            // Adicione mais arquivos conforme necessário
        ];

        foreach ($files as $from => $to) {
            if (File::exists($from)) {
                File::ensureDirectoryExists(dirname($to));

                if (!File::exists($to) || $this->option('force')) {
                    File::copy($from, $to);
                    $this->line("✓ Copiado: {$to}");
                } else {
                    $this->warn("⚠ Arquivo já existe: {$to}");
                }
            }
        }
    }
}