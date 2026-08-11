<?php

use App\Jobs\System\RunArtisanCommandsJob;
use App\Jobs\System\UpdateProjectJob;
use App\Support\SystemCommandProgress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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

    /** @var list<string> */
    private const SUGGESTED_COMMANDS = [
        'cache:clear',
        'route:clear',
        'view:clear',
        'config:clear',
        'optimize',
        'optimize:clear',
        'migrate',
        'queue:restart',
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

    public function runFromSearch(string $command): void
    {
        $this->artisanCommand = $command;
        $this->runArtisan();
    }

    #[Computed]
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * @return Collection<int, array{title: string, value: string, description: string, category: string, default: bool}>
     */
    #[Computed]
    public function availableCommands(): Collection
    {
        /** @var Collection<int, array{name: string, description: string}> $commands */
        $commands = Cache::rememberForever('admin.artisan.commands', function () {
            return collect(Artisan::all())
                ->map(fn ($command, string $name) => [
                    'name' => $name,
                    'description' => $command->getDescription(),
                ])
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        });

        return $commands
            ->reject(fn (array $cmd) => $this->isDangerousCommand($cmd['name']))
            ->map(function (array $cmd) {
                $name = $cmd['name'];
                $category = str_contains($name, ':')
                    ? strstr($name, ':', true) ?: 'general'
                    : 'general';

                return [
                    'title' => $name,
                    'value' => $name,
                    'description' => $cmd['description'],
                    'category' => $category,
                    'default' => in_array($name, self::SUGGESTED_COMMANDS, true),
                ];
            })
            ->values();
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
                <div>
                    <h2 class="text-lg font-semibold text-heading">{{ __('app.function_commands_title') }}</h2>
                    <p class="mt-1 text-sm text-body">{{ __('app.function_commands_help') }}</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
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

                <div
                    wire:ignore
                    x-data="artisanCommandPalette(@js($this->availableCommands))"
                    x-init="init()"
                    @keydown.down.prevent="commandItemActiveNext()"
                    @keydown.up.prevent="commandItemActivePrevious()"
                    @keydown.enter.prevent="commandConfirm()"
                    class="w-full space-y-3"
                    dir="ltr"
                >
                    <div class="flex w-full flex-col overflow-hidden rounded-lg border border-default bg-neutral-primary shadow-sm">
                        <div class="flex items-center gap-2 border-b border-default px-3">
                            <x-lucide-search class="h-4 w-4 shrink-0 text-body" />
                            <input
                                type="text"
                                x-ref="commandInput"
                                x-model="commandSearch"
                                x-bind:disabled="$wire.status === 'running'"
                                class="h-11 w-full border-0 bg-transparent px-1 text-sm text-heading outline-none placeholder:text-body focus:border-0 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="{{ __('app.function_artisan_placeholder') }}"
                                autocomplete="off"
                                autocorrect="off"
                                spellcheck="false"
                            >
                        </div>

                        <div x-ref="commandItemsList" class="max-h-80 overflow-x-hidden overflow-y-auto">
                            <template x-if="commandItemsFiltered.length === 0">
                                <p class="px-4 py-6 text-center text-sm text-body">
                                    {{ __('app.function_artisan_no_match') }}
                                </p>
                            </template>

                            <template x-for="(item, index) in commandItemsFiltered" :key="item.value">
                                <div>
                                    <template x-if="commandShowCategory(item, index)">
                                        <div class="px-3 pt-2 pb-1 text-xs font-medium tracking-wide text-body uppercase" x-text="commandCategoryLabel(item.category)"></div>
                                    </template>

                                    <div class="px-1 pb-0.5">
                                        <button
                                            type="button"
                                            :id="item.value + '-' + commandId"
                                            @click="commandSelect(item)"
                                            @mousemove="commandItemActive = item"
                                            x-bind:disabled="$wire.status === 'running'"
                                            :class="commandItemIsActive(item) ? 'bg-neutral-secondary-soft text-heading' : 'text-heading'"
                                            class="relative flex w-full cursor-pointer select-none items-start gap-2 rounded-md px-2 py-2 text-start text-sm outline-none transition hover:bg-neutral-secondary-soft disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <x-lucide-terminal class="mt-0.5 h-4 w-4 shrink-0 text-body" />
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate font-mono text-sm" x-text="item.title"></span>
                                                <span
                                                    class="block truncate text-xs text-body"
                                                    x-show="item.description"
                                                    x-text="item.description"
                                                ></span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <x-ui.button
                        type="button"
                        color="blue"
                        class="w-full"
                        target="runArtisan,runFromSearch"
                        x-on:click="commandRunFree()"
                        x-bind:disabled="$wire.status === 'running'"
                    >
                        <x-slot:icon>
                            <x-lucide-play class="h-4 w-4 me-2" />
                        </x-slot:icon>
                        {{ __('app.function_run') }}
                    </x-ui.button>
                </div>
            </div>
        </x-fwb.card>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-heading">{{ __('app.function_progress_title') }}</h2>

            <div
                dir="ltr"
                class="overflow-hidden rounded-xl border border-zinc-700/80 bg-zinc-950 shadow-lg shadow-zinc-950/20"
            >
                <div class="flex items-center gap-3 border-b border-zinc-800 bg-zinc-900 px-4 py-2.5">
                    <div class="flex shrink-0 items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-red-500"></span>
                        <span class="size-2.5 rounded-full bg-amber-400"></span>
                        <span class="size-2.5 rounded-full bg-emerald-500"></span>
                    </div>

                    <div class="min-w-0 flex-1 truncate font-mono text-xs text-zinc-400">
                        @if ($currentStep !== '')
                            <span class="text-emerald-400">$</span>
                            <span class="text-zinc-200">{{ $currentStep }}</span>
                        @else
                            <span class="text-zinc-500">{{ __('app.function_progress_idle') }}</span>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <span @class([
                            'inline-flex items-center rounded px-2 py-0.5 font-mono text-[10px] font-medium uppercase tracking-wide',
                            'bg-zinc-800 text-zinc-400' => $status === 'idle',
                            'bg-amber-500/15 text-amber-300' => $status === 'running',
                            'bg-emerald-500/15 text-emerald-300' => $status === 'success',
                            'bg-red-500/15 text-red-300' => $status === 'failed',
                        ])>
                            {{ __('app.function_status_'.$status) }}
                        </span>
                        <span class="font-mono text-[10px] tabular-nums text-zinc-500">{{ $progress }}%</span>
                    </div>
                </div>

                <div class="h-1 w-full bg-zinc-900">
                    <div
                        @class([
                            'h-full transition-all duration-300',
                            'bg-zinc-600' => $status === 'idle',
                            'bg-amber-400' => $status === 'running',
                            'bg-emerald-400' => $status === 'success',
                            'bg-red-400' => $status === 'failed',
                        ])
                        style="width: {{ max(0, min(100, $progress)) }}%"
                    ></div>
                </div>

                <div
                    class="max-h-96 overflow-auto"
                    x-data
                    x-effect="$wire.output; $wire.status; $nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                >
                    <pre class="p-4 text-left font-mono text-xs leading-relaxed whitespace-pre-wrap text-zinc-300">@if ($output !== ''){{ $output }}
@else<span class="text-zinc-600">{{ __('app.function_output_empty') }}</span>
@endif@if ($this->isRunning)<span class="ms-0.5 inline-block h-3.5 w-1.5 animate-pulse bg-emerald-400 align-text-bottom"></span>@endif</pre>
                </div>
            </div>
        </div>
    </div>
</div>
