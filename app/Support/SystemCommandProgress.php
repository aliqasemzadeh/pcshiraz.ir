<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SystemCommandProgress
{
    public static function directory(): string
    {
        return storage_path('app/system-console');
    }

    public static function path(string $runId): string
    {
        return self::directory().DIRECTORY_SEPARATOR.$runId.'.json';
    }

    public static function newRunId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * @return array{
     *     status: string,
     *     progress: int,
     *     current_step: string,
     *     output: string,
     *     started_at: string|null,
     *     finished_at: string|null
     * }
     */
    public static function empty(): array
    {
        return [
            'status' => 'idle',
            'progress' => 0,
            'current_step' => '',
            'output' => '',
            'started_at' => null,
            'finished_at' => null,
        ];
    }

    public static function start(string $runId, string $step = ''): void
    {
        self::write($runId, [
            'status' => 'running',
            'progress' => 0,
            'current_step' => $step,
            'output' => '',
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
        ]);
    }

    public static function step(string $runId, string $step, ?int $progress = null): void
    {
        $data = self::read($runId);
        $data['status'] = 'running';
        $data['current_step'] = $step;

        if ($progress !== null) {
            $data['progress'] = max(0, min(100, $progress));
        }

        self::write($runId, $data);
    }

    public static function appendOutput(string $runId, string $output): void
    {
        $data = self::read($runId);
        $trimmed = trim($output);

        if ($trimmed === '') {
            return;
        }

        $data['output'] = trim($data['output']."\n".$trimmed);
        self::write($runId, $data);
    }

    public static function setProgress(string $runId, int $progress): void
    {
        $data = self::read($runId);
        $data['progress'] = max(0, min(100, $progress));
        self::write($runId, $data);
    }

    public static function finish(string $runId, string $step = ''): void
    {
        $data = self::read($runId);
        $data['status'] = 'success';
        $data['progress'] = 100;
        $data['current_step'] = $step !== '' ? $step : $data['current_step'];
        $data['finished_at'] = now()->toIso8601String();
        self::write($runId, $data);
    }

    public static function fail(string $runId, string $message = ''): void
    {
        $data = self::read($runId);
        $data['status'] = 'failed';
        $data['finished_at'] = now()->toIso8601String();

        if ($message !== '') {
            $data['current_step'] = $message;
            $data['output'] = trim($data['output']."\n".$message);
        }

        self::write($runId, $data);
    }

    /**
     * @return array{
     *     status: string,
     *     progress: int,
     *     current_step: string,
     *     output: string,
     *     started_at: string|null,
     *     finished_at: string|null
     * }
     */
    public static function read(string $runId): array
    {
        if ($runId === '' || ! File::exists(self::path($runId))) {
            return self::empty();
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode(File::get(self::path($runId)), true);

        if (! is_array($decoded)) {
            return self::empty();
        }

        return array_merge(self::empty(), $decoded);
    }

    /**
     * @param  array{
     *     status: string,
     *     progress: int,
     *     current_step: string,
     *     output: string,
     *     started_at: string|null,
     *     finished_at: string|null
     * }  $data
     */
    public static function write(string $runId, array $data): void
    {
        File::ensureDirectoryExists(self::directory());
        File::put(self::path($runId), json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
