<?php

namespace App\Jobs\System;

use App\Support\SystemCommandProgress;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunArtisanCommandsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    /**
     * @param  list<string>  $commands
     */
    public function __construct(
        public string $runId,
        public array $commands,
    ) {}

    public function handle(): void
    {
        $total = count($this->commands);

        if ($total === 0) {
            SystemCommandProgress::fail($this->runId, 'No commands to run.');

            return;
        }

        SystemCommandProgress::start($this->runId, $this->commands[0]);

        foreach ($this->commands as $index => $command) {
            $progress = (int) round(($index / $total) * 100);
            SystemCommandProgress::step($this->runId, $command, $progress);
            SystemCommandProgress::appendOutput($this->runId, "> php artisan {$command}");

            Log::info("Running artisan command via job: {$command}");

            try {
                $exitCode = Artisan::call($command);
                $output = trim(Artisan::output());

                if ($output !== '') {
                    SystemCommandProgress::appendOutput($this->runId, $output);
                }

                if ($exitCode !== 0) {
                    SystemCommandProgress::fail($this->runId, "Command failed with exit code {$exitCode}: {$command}");

                    return;
                }
            } catch (Throwable $exception) {
                Log::error('Artisan command job failed.', [
                    'command' => $command,
                    'exception' => $exception->getMessage(),
                ]);
                SystemCommandProgress::fail($this->runId, $exception->getMessage());

                return;
            }
        }

        SystemCommandProgress::finish($this->runId, 'Done');
    }

    public function failed(?Throwable $exception): void
    {
        SystemCommandProgress::fail($this->runId, $exception?->getMessage() ?? 'Job failed.');
    }
}
