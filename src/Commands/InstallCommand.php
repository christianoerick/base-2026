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

        $this->call('vendor:publish', [
            '--tag' => 'admin-config',
            '--force' => $this->option('force')
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'admin-models',
            '--force' => $this->option('force')
        ]);

        //$this->copyCustomFiles();
        $this->checkConfigFiles();

        /*
        if ($this->confirm('Deseja executar as migrations agora?', true)) {
            $this->call('migrate');
        }
        /* */

        $this->info('✅ Instalação concluída com sucesso!');
        $this->line('Execute: php artisan admin:install para reinstalar se necessário');
    }

    protected function copyCustomFiles()
    {
        $files = [
            __DIR__.'/../../stubs/app/Providers/AppServiceProvider.php'
            => app_path('Providers/AppServiceProvider.php'),
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

    protected function checkConfigFiles()
    {
        $file = base_path('config/filesystems.php');
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if (!str_contains($content, 'B2_BUCKET')) {
                $content_new = <<<'EOD'
                's3' => [
                            'driver' => 's3',
                            'key' => env('AWS_ACCESS_KEY_ID'),
                            'secret' => env('AWS_SECRET_ACCESS_KEY'),
                            'region' => env('AWS_DEFAULT_REGION'),
                            'bucket' => env('AWS_BUCKET'),
                            'url' => env('AWS_URL'),
                            'endpoint' => env('AWS_ENDPOINT'),
                            'visibility' => 'public',
                            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
                            'throw' => false,
                            'report' => false,
                        ],

                        'b2' => [
                            'driver' => 's3',
                            'key' => env('B2_ACCESS_KEY_ID'),
                            'secret' => env('B2_SECRET_ACCESS_KEY'),
                            'region' => env('B2_DEFAULT_REGION'),
                            'bucket' => env('B2_BUCKET'),
                            'endpoint' => 'https://s3.'.env('B2_DEFAULT_REGION').'.backblazeb2.com',
                            'visibility' => 'public',
                            'version' => 'latest',
                            'debug' => false,
                            'throw' => true,
                            'report' => true,
                            'options' => [
                                'StorageClass' => 'STANDARD',
                            ],
                            'request_checksum_calculation' => 'when_required',
                            'response_checksum_validation' => 'when_required',
                        ],

                EOD;

                $regex = "/'s3'\s*=>\s*\[.*?],\s*/s";

                if (preg_match($regex, $content)) {
                    $novoConteudo = preg_replace($regex, $content_new . "\n\t", $content);
                    file_put_contents($file, $novoConteudo);
                }
            }
        }
    }
}
