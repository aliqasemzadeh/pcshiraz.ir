<?php

use App\Jobs\System\RunArtisanCommandsJob;
use App\Jobs\System\UpdateProjectJob;
use App\Support\SystemCommandProgress;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new #[Layout('layouts.panels')] class extends Component
{
    public string $runId = '';

    public string $status = 'idle';

    public int $progress = 0;

    public string $currentStep = '';

    public string $output = '';

    public string $artisanCommand = '';

    public bool $notified = false;

    /** @var list<string> */
    private const DANGEROUS_COMMANDS = [
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'db:wipe',
        'db:seed',
        'tinker',
        'serve',
        'queue:work',
        'queue:listen',
        'pail',
        'down',
        'env:encrypt',
        'env:decrypt',
        'key:generate',
    ];

    public function mount(): void
    {
        $this->runId = (string) session('system_console_run_id', '');

        if ($this->runId === '') {
            $this->resetProgressState();

            return;
        }

        $this->syncFromProgress();
        $this->notified = $this->status !== 'running';
    }

    public function refreshProgress(): void
    {
        if ($this->runId === '') {
            return;
        }

        $data = SystemCommandProgress::read($this->runId);

        $this->status = $data['status'];
        $this->progress = (int) $data['progress'];
        $this->currentStep = $data['current_step'];
        $this->output = $data['output'];

        if (! $this->notified && in_array($this->status, ['success', 'failed'], true)) {
            $this->notified = true;

            if ($this->status === 'success') {
                Toaster::success(__('app.function_finished'));
            } else {
                Toaster::error(__('app.function_failed'));
            }
        }
    }

    public function quickUpdate(): void
    {
        $this->dispatchUpdate(runComposer: false);
    }

    public function fullUpdate(): void
    {
        $this->dispatchUpdate(runComposer: true);
    }

    public function clearRoute(): void
    {
        $this->dispatchArtisanCommands(['route:clear']);
    }

    public function clearCache(): void
    {
        $this->dispatchArtisanCommands(['cache:clear']);
    }

    public function clearView(): void
    {
        $this->dispatchArtisanCommands(['view:clear']);
    }

    public function clearConfig(): void
    {
        $this->dispatchArtisanCommands(['config:clear']);
    }

    public function clearAll(): void
    {
        $this->dispatchArtisanCommands([
            'route:clear',
            'cache:clear',
            'view:clear',
            'config:clear',
        ]);
    }

    public function optimize(): void
    {
        $this->dispatchArtisanCommands(['optimize']);
    }

    public function runArtisan(): void
    {
        $command = $this->normalizeArtisanCommand($this->artisanCommand);

        if ($command === '') {
            Toaster::error(__('app.function_artisan_empty'));

            return;
        }

        if ($this->isDangerousCommand($command)) {
            Toaster::error(__('app.function_artisan_forbidden'));

            return;
        }

        $this->dispatchArtisanCommands([$command]);
    }

    #[Computed]
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    protected function dispatchUpdate(bool $runComposer): void
    {
        if (! $this->ensureNotRunning()) {
            return;
        }

        $this->beginRun($runComposer ? 'full update' : 'quick update');

        UpdateProjectJob::dispatch($this->runId, $runComposer);

        Toaster::success(__('app.function_started'));
    }

    /**
     * @param  list<string>  $commands
     */
    protected function dispatchArtisanCommands(array $commands): void
    {
        if (! $this->ensureNotRunning()) {
            return;
        }

        $this->beginRun($commands[0] ?? '');

        RunArtisanCommandsJob::dispatch($this->runId, $commands);

        Toaster::success(__('app.function_started'));
    }

    protected function ensureNotRunning(): bool
    {
        if ($this->status === 'running') {
            Toaster::warning(__('app.function_already_running'));

            return false;
        }

        return true;
    }

    protected function beginRun(string $step): void
    {
        $this->runId = SystemCommandProgress::newRunId();
        $this->notified = false;
        session(['system_console_run_id' => $this->runId]);
        SystemCommandProgress::start($this->runId, $step);
        $this->syncFromProgress();
    }

    protected function syncFromProgress(): void
    {
        $data = SystemCommandProgress::read($this->runId);

        $this->status = $data['status'];
        $this->progress = (int) $data['progress'];
        $this->currentStep = $data['current_step'];
        $this->output = $data['output'];
    }

    protected function resetProgressState(): void
    {
        $this->runId = '';
        $this->status = 'idle';
        $this->progress = 0;
        $this->currentStep = '';
        $this->output = '';
        $this->notified = false;
    }

    protected function normalizeArtisanCommand(string $command): string
    {
        $command = trim($command);
        $command = preg_replace('/^(php\s+)?artisan\s+/i', '', $command) ?? '';

        return trim($command);
    }

    protected function isDangerousCommand(string $command): bool
    {
        if (preg_match('/[;&|`]|\&\&|\|\|/', $command) === 1) {
            return true;
        }

        $name = strtolower(strtok($command, ' ') ?: $command);

        return in_array($name, self::DANGEROUS_COMMANDS, true);
    }
};
?>

<x-slot name="title">{{ __('general.functions') }} - {{ config('app.name') }}</x-slot>

<div
    @if ($this->isRunning)
        wire:poll.2s="refreshProgress"
    @endif
>
    <x-fwb.breadcrumb class="mb-4">
        <x-fwb.breadcrumb.item home>{{ __('general.administrator') }}</x-fwb.breadcrumb.item>
        <x-fwb.breadcrumb.item>{{ __('general.functions') }}</x-fwb.breadcrumb.item>
    </x-fwb.breadcrumb>

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-heading">{{ __('general.functions') }}</h1>
            <p class="mt-1 text-sm text-body">{{ __('app.function_subtitle') }}</p>
        </div>

        <x-fwb.card>
            <div class="space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-heading">{{ __('app.function_update_title') }}</h2>
                    <p class="mt-1 text-sm text-body">{{ __('app.function_update_help') }}</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-default p-4">
                        <h3 class="font-medium text-heading">{{ __('app.function_quick_update') }}</h3>
                        <p class="mt-1 text-sm text-body">{{ __('app.function_quick_update_help') }}</p>
                        <div class="mt-4">
                            <x-ui.button
                                type="button"
                                color="teal"
                                class="w-full"
                                target="quickUpdate"
                                wire:click="quickUpdate"
                                wire:confirm="{{ __('general.are_you_sure') }}"
                                :disabled="$this->isRunning"
                            >
                                <x-slot:icon>
                                    <x-lucide-refresh-cw class="h-4 w-4 me-2" />
                                </x-slot:icon>
                                {{ __('app.function_quick_update') }}
                            </x-ui.button>
                        </div>
                    </div>

                    <div class="rounded-lg border border-default p-4">
                        <h3 class="font-medium text-heading">{{ __('app.function_full_update') }}</h3>
                        <p class="mt-1 text-sm text-body">{{ __('app.function_full_update_help') }}</p>
                        <div class="mt-4">
                            <x-ui.button
                                type="button"
                                color="orange"
                                class="w-full"
                                target="fullUpdate"
                                wire:click="fullUpdate"
                                wire:confirm="{{ __('general.are_you_sure') }}"
                                :disabled="$this->isRunning"
                            >
                                <x-slot:icon>
                                    <x-lucide-package class="h-4 w-4 me-2" />
                                </x-slot:icon>
                                {{ __('app.function_full_update') }}
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        </x-fwb.card>

        <x-fwb.card>
            <div class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-heading">{{ __('app.function_commands_title') }}</h2>
                        <p class="mt-1 text-sm text-body">{{ __('app.function_commands_help') }}</p>
                    </div>

                    <x-ui.button
                        type="button"
                        color="red"
                        target="clearAll"
                        wire:click="clearAll"
                        wire:confirm="{{ __('general.are_you_sure') }}"
                        :disabled="$this->isRunning"
                    >
                        <x-slot:icon>
                            <x-lucide-trash-2 class="h-4 w-4 me-2" />
                        </x-slot:icon>
                        {{ __('app.function_clear_all') }}
                    </x-ui.button>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <x-ui.button
                        type="button"
                        color="cyan"
                        class="w-full"
                        target="clearRoute"
                        wire:click="clearRoute"
                        :disabled="$this->isRunning"
                    >
                        route:clear
                    </x-ui.button>

                    <x-ui.button
                        type="button"
                        color="sky"
                        class="w-full"
                        target="clearCache"
                        wire:click="clearCache"
                        :disabled="$this->isRunning"
                    >
                        cache:clear
                    </x-ui.button>

                    <x-ui.button
                        type="button"
                        color="indigo"
                        class="w-full"
                        target="clearView"
                        wire:click="clearView"
                        :disabled="$this->isRunning"
                    >
                        view:clear
                    </x-ui.button>

                    <x-ui.button
                        type="button"
                        color="violet"
                        class="w-full"
                        target="clearConfig"
                        wire:click="clearConfig"
                        :disabled="$this->isRunning"
                    >
                        config:clear
                    </x-ui.button>

                    <x-ui.button
                        type="button"
                        color="green"
                        class="w-full"
                        target="optimize"
                        wire:click="optimize"
                        :disabled="$this->isRunning"
                    >
                        optimize
                    </x-ui.button>
                </div>
            </div>
        </x-fwb.card>

        <x-fwb.card>
            <div class="space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-heading">{{ __('app.function_artisan_title') }}</h2>
                    <p class="mt-1 text-sm text-body">{{ __('app.function_artisan_help') }}</p>
                </div>

                <form wire:submit="runArtisan" class="flex flex-col gap-3 sm:flex-row">
                    <x-fwb.input
                        wire:model="artisanCommand"
                        class="w-full"
                        placeholder="{{ __('app.function_artisan_placeholder') }}"
                        :disabled="$this->isRunning"
                    />

                    <x-ui.button
                        type="submit"
                        color="blue"
                        class="w-full sm:w-auto"
                        target="runArtisan"
                        :disabled="$this->isRunning"
                    >
                        <x-slot:icon>
                            <x-lucide-terminal class="h-4 w-4 me-2" />
                        </x-slot:icon>
                        {{ __('app.function_run') }}
                    </x-ui.button>
                </form>
            </div>
        </x-fwb.card>

        <x-fwb.card>
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-heading">{{ __('app.function_progress_title') }}</h2>
                        <p class="mt-1 text-sm text-body">
                            @if ($currentStep !== '')
                                {{ $currentStep }}
                            @else
                                {{ __('app.function_progress_idle') }}
                            @endif
                        </p>
                    </div>

                    <span @class([
                        'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
                        'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' => $status === 'idle',
                        'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' => $status === 'running',
                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' => $status === 'success',
                        'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' => $status === 'failed',
                    ])>
                        {{ __('app.function_status_'.$status) }}
                    </span>
                </div>

                <div class="h-3 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                    <div
                        class="h-full rounded-full bg-brand transition-all duration-300"
                        style="width: {{ max(0, min(100, $progress)) }}%"
                    ></div>
                </div>

                <div class="flex items-center justify-between text-xs text-body">
                    <span>{{ $progress }}%</span>
                    @if ($this->isRunning)
                        <span class="inline-flex items-center gap-1">
                            <x-fwb.spinner color="blue" size="xs" />
                            {{ __('general.working') }}
                        </span>
                    @endif
                </div>

                <pre dir="ltr" class="max-h-96 overflow-auto rounded-lg border border-default bg-slate-950 p-4 text-left text-xs leading-relaxed whitespace-pre-wrap text-slate-100">{{ $output !== '' ? $output : __('app.function_output_empty') }}</pre>
            </div>
        </x-fwb.card>
    </div>
</div>
