<?php

namespace App\Jobs\System;

use App\Support\SystemCommandProgress;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Throwable;

class UpdateProjectJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public function __construct(
        public string $runId,
        public bool $runComposer = true,
    ) {}

    public function handle(): void
    {
        SystemCommandProgress::start($this->runId, 'git pull');

        if (! $this->gitPull()) {
            SystemCommandProgress::fail($this->runId, 'Git pull failed.');

            return;
        }

        $steps = $this->runComposer
            ? ['composer', 'migrate', 'permissions', 'cache', 'theme', 'queue']
            : ['migrate', 'permissions', 'cache', 'theme', 'queue'];
        $stepIndex = 0;
        $total = count($steps) + 1; // +1 for git already done

        if ($this->runComposer) {
            $stepIndex++;
            SystemCommandProgress::step($this->runId, 'composer install', (int) round(($stepIndex / $total) * 100));
            if (! $this->runComposerUpdate()) {
                SystemCommandProgress::fail($this->runId, 'Composer install failed.');

                return;
            }
        }

        $stepIndex++;
        SystemCommandProgress::step($this->runId, 'migrate', (int) round(($stepIndex / $total) * 100));
        $this->runMigrations();

        $stepIndex++;
        SystemCommandProgress::step($this->runId, 'permissions:sync', (int) round(($stepIndex / $total) * 100));
        $this->runArtisan('permissions:sync');

        $stepIndex++;
        SystemCommandProgress::step($this->runId, 'clear caches', (int) round(($stepIndex / $total) * 100));
        $this->clearCache();
        $this->clearRoute();
        $this->clearView();
        $this->clearConfig();

        $stepIndex++;
        SystemCommandProgress::step($this->runId, 'npm run build', (int) round(($stepIndex / $total) * 100));
        $this->addTheme();

        $stepIndex++;
        SystemCommandProgress::step($this->runId, 'queue:restart', (int) round(($stepIndex / $total) * 100));
        $this->restartQueue();

        SystemCommandProgress::finish($this->runId, 'Done');
    }

    public function failed(?Throwable $exception): void
    {
        SystemCommandProgress::fail($this->runId, $exception?->getMessage() ?? 'Update job failed.');
    }

    protected function gitPull(): bool
    {
        Log::info('Starting project update (git pull)...');
        SystemCommandProgress::appendOutput($this->runId, '> git pull');

        $process = Process::forever()
            ->path(base_path())
            ->run('git pull');

        if ($process->successful()) {
            $output = trim($process->output());
            Log::info("Git pull successful:\n".$output);
            SystemCommandProgress::appendOutput($this->runId, $output !== '' ? $output : 'Git pull successful.');

            return true;
        }

        $error = trim($process->errorOutput() ?: $process->output());
        Log::error("Git pull failed:\n".$error);
        SystemCommandProgress::appendOutput($this->runId, $error);

        return false;
    }

    protected function runMigrations(): void
    {
        $this->runArtisan('migrate', ['--force' => true]);
    }

    protected function runComposerUpdate(): bool
    {
        Log::info('Running composer install...');
        SystemCommandProgress::appendOutput($this->runId, '> composer install --no-dev --optimize-autoloader --no-interaction');

        try {
            $process = Process::forever()
                ->path(base_path())
                ->run($this->composerCommand().' install --no-dev --optimize-autoloader --no-interaction');

            if ($process->successful()) {
                $output = trim($process->output());
                Log::info("Composer install successful:\n".$output);
                SystemCommandProgress::appendOutput($this->runId, $output !== '' ? $output : 'Composer install successful.');

                return true;
            }

            $error = trim($process->errorOutput() ?: $process->output());
            Log::error("Composer install failed:\n".$error);
            SystemCommandProgress::appendOutput($this->runId, $error);

            return false;
        } catch (Throwable $exception) {
            Log::error('Composer install failed: '.$exception->getMessage());
            SystemCommandProgress::appendOutput($this->runId, $exception->getMessage());

            return false;
        }
    }

    protected function clearCache(): void
    {
        $this->runArtisan('cache:clear');
    }

    protected function clearRoute(): void
    {
        $this->runArtisan('route:clear');
    }

    protected function clearView(): void
    {
        $this->runArtisan('view:clear');
    }

    protected function clearConfig(): void
    {
        $this->runArtisan('config:clear');
    }

    protected function addTheme(): void
    {
        Log::info('Building theme assets (npm run build)...');
        SystemCommandProgress::appendOutput($this->runId, '> npm run build');

        try {
            $process = Process::forever()
                ->path(base_path())
                ->run($this->npmCommand().' run build');

            if ($process->successful()) {
                $output = trim($process->output());
                Log::info("Theme build successful:\n".$output);
                SystemCommandProgress::appendOutput($this->runId, $output !== '' ? $output : 'Theme build successful.');

                return;
            }

            $error = trim($process->errorOutput() ?: $process->output());
            Log::error("Theme build failed:\n".$error);
            SystemCommandProgress::appendOutput($this->runId, $error);
        } catch (Throwable $exception) {
            Log::error('Theme build failed: '.$exception->getMessage());
            SystemCommandProgress::appendOutput($this->runId, $exception->getMessage());
        }
    }

    protected function restartQueue(): void
    {
        $this->runArtisan('queue:restart');
    }

    protected function runArtisan(string $command, array $parameters = []): void
    {
        $display = $command;

        foreach ($parameters as $key => $value) {
            if (is_bool($value) && $value) {
                $display .= ' '.$key;
            } elseif (! is_bool($value)) {
                $display .= ' '.$key.'='.$value;
            }
        }

        Log::info("{$command}...");
        SystemCommandProgress::appendOutput($this->runId, "> php artisan {$display}");

        Artisan::call($command, $parameters);

        $output = trim(Artisan::output());
        Log::info("{$command}:\n".$output);

        if ($output !== '') {
            SystemCommandProgress::appendOutput($this->runId, $output);
        }
    }

    protected function npmCommand(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'npm.cmd' : 'npm';
    }

    protected function composerCommand(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'composer.bat' : 'composer';
    }
}
