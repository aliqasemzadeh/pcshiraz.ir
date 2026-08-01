---
description: 
alwaysApply: true
---

# Project Role & Context
You are an expert full-stack developer working on a Laravel project. Your task is to generate code that strictly adheres to the following project guidelines, tech stack, and architectural rules.

## 1. Tech Stack & Environment
*   **Backend:** PHP 8.4, Laravel 13
*   **Frontend:** TailwindCSS, AlpineJS (for UI interactions), Flowbite JS (for interactive UI like dropdown, tooltip, datepicker)
*   **Livewire:** Version 4 (Do NOT use Volt; use standard `Livewire\Component`)
*   **UI Library:** Flowbite (https://flowbite.com/) — full LLM docs: https://raw.githubusercontent.com/themesberg/flowbite/refs/heads/main/llms-full.txt
*   **Blade UI components:** `themesberg/flowbite-laravel-components` (installed from [aliqasemzadeh fork](https://github.com/aliqasemzadeh/flowbite-laravel-components) via Composer VCS for Laravel 13). Use `<x-fwb.*>` for presentational UI.
*   **Icons:** Lucide via `mallardduck/blade-lucide-icons` (https://lucide.dev/icons). Use Blade components: `<x-lucide-pencil class="w-4 h-4" />`. Icon names use kebab-case matching Lucide (e.g. `pencil`, `trash-2`, `plus`, `search`, `chevron-down`). Prefer Lucide in the `icon` slot of `x-fwb.*` over `fwb-icon`.
*   **Tables:** `power-components/livewire-powergrid` v6 (https://github.com/Power-Components/livewire-powergrid) — demo/reference: https://github.com/Power-Components/powergrid-demo and https://demo.livewire-powergrid.com. Docs: https://livewire-powergrid.com
*   **Modals:** `elegantly/livewire-modal` (https://elegantly.dev/livewire-modal) — NOT Flowbite modals/drawers / `x-fwb.modal` / `x-fwb.drawer` for CRUD forms.
*   **Toasts:** `masmerise/livewire-toaster` — use `Masmerise\Toaster\Toaster` facade. Hub views are re-skinned to match Flowbite toast. Do not call `<x-fwb.toast>` from Livewire actions.
*   **Do NOT use FluxUI** (`flux:*` components, `Flux::toast`, `Flux::modal`, `php artisan flux:icon`).
*   **Do NOT hand-roll HTML `<table>` lists** for CRUD indexes — always use PowerGrid.

## 2. Architecture & File Structure
*   **Single-File Component Architecture:** ALWAYS write Livewire page/form/modal components as Single-File Components. Place all PHP logic inside a `<?php ... ?>` block at the top of the `.blade.php` file.
*   **EXCEPTION — PowerGrid Tables:** Table components MUST be class-based and extend `PowerComponents\LivewirePowerGrid\PowerGridComponent`. Create with `php artisan powergrid:create`. Place them under `app/Livewire/` (e.g. `app/Livewire/UserTable.php`, `app/Livewire/Administrator/UserTable.php`). Keep the `*Table` suffix so Tailwind `@source` in `resources/css/app.css` picks them up.
*   **File Location:** Save page/form SFCs in `resources/views/components/` or `resources/views/pages/`. Save PowerGrid tables in `app/Livewire/`.
*   **Root Node:** Ensure the HTML portion of SFC components always has a single root wrapping element.
*   **Separation of Concerns:** Use AlpineJS for UI manipulation and Livewire strictly for Backend logic.
*   **Livewire Inclusions:** When loading a Livewire component inside a view, always pass a key: `<livewire:component-name :key="$componentId" />`.
*   **Pages namespace:** For Livewire pages use `pages::` and SCF (single-file components). Do not convert pages to class-based components (PowerGrid tables are the only class-based exception).

## 3. Database & Eloquent Models
*   **Attributes:** Use PHP Attributes like `#[Fillable([])]` and `#[Hidden]` from `Illuminate\Database\Eloquent\Attributes` instead of the traditional `$fillable` or `$hidden` arrays.
*   **Relations:** Always explicitly define Eloquent relationships.
*   **Performance:** Heavily optimize queries and strictly avoid N+1 problems. Use cache when appropriate.

## 4. Livewire Logic & State Management
*   **Computed Properties:** Use `#[Computed]` attributes to load data (https://livewire.laravel.com/docs/4.x/attribute-computed).
*   **Forms:** For model forms, use Livewire Form Objects (https://livewire.laravel.com/docs/4.x/forms). Extract them using `php artisan livewire:form ModelForm`. Use a `setModel` method to populate data.
*   **Live Binding:** NEVER use `wire:model.live` in forms unless explicitly requested.
*   **Navigation:** Always use `wire:navigate` on internal links to ensure SPA-like page transitions without full page reloads.
*   **Events:** Never use `protected $listeners`. Use `Livewire\Attributes\On;` and `$this->dispatch('event-name');`.
*   **Event Naming:** Use full explicit names (e.g., `panels.administrator.learning-management.school.edit.assign-data`).
*   **Notifications:** After any Livewire action, trigger a toast:
    ```php
    use Masmerise\Toaster\Toaster;

    Toaster::success(__('general.saved'));
    Toaster::error(__('general.error'));
    Toaster::info(__('general.info'));
    Toaster::warning(__('general.warning'));
    ```

## 5. Layout Prerequisites
In the main layout (`body` end), always include:
```html
<x-toaster-hub />
<livewire:modal />
```

In `resources/js/app.js`:
```js
import '../../vendor/masmerise/livewire-toaster/resources/js';
```

Ensure Tailwind content / `@source` includes (PowerGrid is already wired in `resources/css/app.css`):
*   PowerGrid Tailwind 4 CSS import + `@source` for `app/Livewire/*Table.php`, `app/Support/PowerGrid/FlowbiteTheme.php`, vendor views, and stock `Tailwind.php`
*   `./vendor/elegantly/livewire-modal/resources/views/**/*.blade.php`
*   Published modal overrides: `resources/views/vendor/livewire-modal/**/*.blade.php` (adds logical `start` / `end` positions)
*   Published toaster views under `resources/views/vendor/toaster/` (and/or vendor toaster views)
*   `./vendor/themesberg/flowbite-laravel-components/resources/views/**/*.blade.php`
*   `./vendor/themesberg/flowbite-laravel-components/src/**/*.php`
*   Flowbite theme: `@import 'flowbite/src/themes/default';` + `@plugin "flowbite/plugin"`

**Brand / PowerGrid colors:** In `resources/css/app.css`, remap `--color-pg-primary-*`, `--color-primary-*`, and `--color-pg-secondary-*` to the indigo brand scale (same as teal→indigo remap). Do **not** leave PowerGrid’s default blue/gray palette. After changing tokens or `FlowbiteTheme` class strings, run `npm run build` (or `npm run dev`).

**Flowbite JS (REQUIRED):** Call `initFlowbite()` on `DOMContentLoaded`, `livewire:navigated`, `pg-livewire-request-finished`, and `livewire:morph.updated` so tooltips/dropdowns work after PowerGrid pagination/search.

## 6. Flowbite Component Rules & UI Innovation
*   **Innovative UI/UX:** Build modern, clean UIs with Flowbite patterns + Tailwind utility classes (smart empty states, loading transitions, clean alignment, modern spacing).
*   Prefer `<x-fwb.*>` Blade components for static/presentational UI (`alert`, `badge`, `card`, `button`, `accordion`, `tabs`, `breadcrumb`, `input`, etc.). Fall back to Flowbite HTML/class patterns from https://flowbite.com/ and the llms-full.txt guide when a component does not fit.
*   **Do NOT use** `x-fwb.modal` / `x-fwb.drawer` for CRUD (keep `elegantly/livewire-modal`), `x-fwb.table` for CRUD lists (keep PowerGrid), or `x-fwb.layouts.*` (keep `layouts.app` / `layouts.panels` / `layouts.auth`).
*   `default_color` is `indigo` in `config/flowbite-blade.php` — match the brand palette unless intentionally overriding.

### Layout & Pages
*   **Page Titles:** Use `<x-slot name="title">Page Title - {{ config('app.name') }}</x-slot>`.
*   **Breadcrumbs:** Prefer `<x-fwb.breadcrumb>` / `<x-fwb.breadcrumb.item>`. Fall back to Flowbite breadcrumb markup (`nav` + `ol`) when needed.
*   **Cards (REQUIRED for PowerGrid indexes):** Always wrap the PowerGrid include in `<x-fwb.card>` — the card provides the Flowbite table outer frame (`bg-neutral-primary-soft border border-default rounded-base shadow-xs`). Do NOT put a second border/shadow on the PowerGrid wrapper. Do NOT build a separate manual search box above PowerGrid — use PowerGrid header search.

### Tables & Lists (`livewire-powergrid`)
*   **Mandatory:** All index/list tables MUST use PowerGrid. Never build custom Blade `<table>` CRUD lists. Do **not** use `x-fwb.table` for CRUD indexes — PowerGrid is the list engine; visual style comes from `FlowbiteTheme`.
*   **Theme (REQUIRED):** Config must use `App\Support\PowerGrid\FlowbiteTheme` (`config/livewire-powergrid.php`). Visual target is the Flowbite products/caption table (Data Display) — see https://github.com/themesberg/flowbite-laravel-components#data-display. Do **not** switch back to stock `Tailwind`/`DaisyUI`/`Bootstrap5` for CRUD tables. Do **not** override row/header background classes per table unless there is an explicit product exception.
*   **Row / header look (REQUIRED):** Built into `FlowbiteTheme` to match the Flowbite products table sample:
    *   `thead`: `bg-neutral-secondary-medium border-b border-t border-default-medium`
    *   `tr`: uniform `bg-neutral-primary-soft border-b border-default` (not striped)
    *   `th` / `td`: `px-6 py-3` / `px-6 py-4`
    *   Outer frame via `<x-fwb.card>` (not duplicated on PowerGrid `layout.div`)
    Caption is page title / breadcrumb outside the table — do not invent PowerGrid captions unless product asks.
*   **Create:** `php artisan powergrid:create` → class under `app/Livewire/` extending `PowerGridComponent`.
*   **Docs / patterns:** Follow https://livewire-powergrid.com and the official demo https://github.com/Power-Components/powergrid-demo (e.g. Search With Relationship).
*   **Unique table name:** Always set `public string $tableName = 'usersTable';` (CamelCase, unique per page). Required for refresh events.
*   **Setup:** Always enable header search + footer pagination/record count:
    ```php
    public function setUp(): array
    {
        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(config('main.per_page'))
                ->showRecordCount(),
        ];
    }
    ```
*   **Datasource:** Return an Eloquent `Builder`. Eager-load every relation used in `fields()` / columns to avoid N+1 (`->with(['category', 'chef'])`).
*   **Searchable columns (REQUIRED):** Every user-facing text column that should be found via the global search MUST chain `->searchable()` on `Column::make(...)`.
*   **Relationship search (REQUIRED):** If any searchable column comes from a relation (or you display related data), you MUST declare `relationSearch()` mapping relation => searchable DB columns. Nested relations are supported. Without this, relation fields will NOT be searched.
    ```php
    public function relationSearch(): array
    {
        return [
            'category' => [
                'name',
            ],
            'chef' => [
                'name',
            ],
            // nested example:
            // 'kitchen' => [
            //     'name',
            //     'chef' => ['name'],
            // ],
        ];
    }
    ```
*   **Relation fields:** Add display fields via closures in `fields()`, mark the column `->searchable()`, and keep `relationSearch()` in sync with the real DB columns on those relations.
*   **Relation filters:** When filtering by a relation column, use `Filter::...()->filterRelation('relation', 'column')` AND keep that relation in `relationSearch()`.
*   **Actions column (REQUIRED):** Add `Column::action(__('general.actions'))` and implement **`actionsFromView($row)`** returning a Blade view with Flowbite tooltips. Prefer dispatching `modal-open` (edit/delete) instead of separate routes.
*   **Do NOT use `actions()` + `Button::tooltip()` for styled tooltips:** PowerGrid’s `->tooltip()` only sets the native HTML `title` attribute. Alpine `x-html` action buttons also cannot emit Flowbite’s two-element tooltip markup. If both `actions()` and `actionsFromView()` exist, **both render** (duplicate buttons) — use only `actionsFromView()`.
*   **Shared row actions:** Prefer `resources/views/components/powergrid/row-actions.blade.php` (`<x-fwb.tooltip>` + Lucide icon buttons with brand/danger classes):
    ```php
    use Illuminate\View\View;

    public function actionsFromView(User $row): View
    {
        return view('components.powergrid.row-actions', [
            'row' => $row,
            'editModal' => 'user.edit',
            'deleteModal' => 'user.delete',
            'idProp' => 'userId',
        ]);
    }
    ```
*   **Include on pages:**
    ```html
    <livewire:user-table :key="'user-table'" />
    ```
*   **Refresh after CRUD:** From create/edit/delete components dispatch PowerGrid's refresh event using the exact `$tableName`:
    ```php
    $this->dispatch('pg:eventRefresh-usersTable');
    ```
    Do not invent generic `refresh-data` listeners for tables.

### Modals (`elegantly/livewire-modal`)
*   **Create / Edit:** Always use **slideover**. Never redirect to separate create/edit routes.
*   **Slideover side (REQUIRED — direction-aware):** Do **not** hardcode `position="right"`. Use logical positions from published views (`resources/views/vendor/livewire-modal/`):
    *   LTR (`__('general.direction') === 'ltr'`) → `position="start"`
    *   RTL (`__('general.direction') === 'rtl'`) → `position="end"`
    ```blade
    position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
    ```
    Published `modal.blade.php` maps `start` → `me-auto` and `end` → `ms-auto`. Keep physical `left` / `right` only when intentionally forcing a physical edge.
*   **Delete confirmation:** Use a **centered** modal (`position="center"`).
*   **Wrapper:** Modal SFCs should root with `<x-livewire-modal::stack>` (no extra useless wrapper). Content goes inside `slideover` or `modal`.
*   **Component naming:** Livewire modal component names use dot notation (e.g. `user.create`, `user.edit`).
*   **Open (Blade / Alpine):**
    ```html
    <button type="button" x-modal:open="{ modal: 'user.create' }">...</button>
    <button type="button" x-modal:open="{ modal: 'user.edit', props: { userId: {{ $user->id }} } }">...</button>
    ```
*   **Open / Close (Livewire PHP):**
    ```php
    $this->dispatch('modal-open', modal: 'user.create');
    $this->dispatch('modal-open', modal: 'user.edit', props: ['userId' => $user->id]);
    $this->dispatch('modal-close');
    $this->dispatch('modal-close-all');
    ```
*   **Close (Blade):** `<button type="button" x-modal:close>...</button>`
*   **Create/Edit buttons:** Full-width submit only (`w-full`). No Cancel button required (user closes via overlay / close control).
*   **Delete modal:** Include Cancel (`x-modal:close`) + Danger Delete button.

**Create/Edit slideover skeleton:**
```html
<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.create_user') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            <div>
                <x-fwb.input wire:model="form.name" :label="__('general.name')" type="text" />
                @error('form.name')
                    <p class="mt-2 text-sm text-fg-danger-strong">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
```

**Delete modal skeleton:**
```html
<x-livewire-modal::stack>
    <x-livewire-modal::modal
        position="center"
        class="w-full max-w-md overflow-auto rounded-lg bg-white p-5 dark:bg-gray-800"
    >
        <h3 class="mb-2 text-lg font-semibold text-heading">
            {{ __('general.delete_confirmation') }}
        </h3>
        <p class="mb-6 text-sm text-body">
            {{ __('general.delete_warning_message') }}
            <br>
            {{ __('general.action_cannot_be_reversed') }}
        </p>
        <div class="flex justify-end gap-2">
            <x-ui.button type="button" color="light" outline :loading="false" x-modal:close>
                {{ __('general.cancel') }}
            </x-ui.button>
            <x-ui.button type="button" color="red" target="delete" wire:click="delete">
                {{ __('general.delete') }}
            </x-ui.button>
        </div>
    </x-livewire-modal::modal>
</x-livewire-modal::stack>
```

### Forms & Inputs (`x-fwb.*` — REQUIRED)
*   **ALWAYS** use Flowbite Blade form components from `themesberg/flowbite-laravel-components` (see [Forms](https://github.com/themesberg/flowbite-laravel-components#forms)):
    *   `<x-fwb.input>` — text, email, number, password, etc.
    *   `<x-fwb.textarea>`
    *   `<x-fwb.select>`
    *   `<x-fwb.checkbox>` / `<x-fwb.radio>`
    *   `<x-fwb.toggle>` — booleans / switches
    *   `<x-fwb.range>`
    *   `<x-fwb.floating-label>` — when floating labels are needed
    *   **File uploads:** `<x-ui.file-input>` (wraps `<x-fwb.file-input>` + Livewire upload progress bar). Do **not** use bare `<x-fwb.file-input>` for Livewire uploads.
*   Do **not** hand-roll raw `<input>` / `<select>` / `<textarea>` with Flowbite utility classes when an `x-fwb.*` form component exists.
*   Always show labels (via component `:label`) and `@error` / Livewire error messages under fields.
*   **Numbers:** `<x-fwb.input type="number" ...>`.
*   **Selects:** Prefer `<x-fwb.select searchable>` when available; for DB-driven options use Livewire computed / backend search.
*   **Dates:** Flowbite Datepicker (`datepicker` data attributes) + Jalali via `morilog/jalali`. All dates must be Jalali.
*   **File upload progress (REQUIRED):** `<x-ui.file-input wire:model="form.logo" :label="__('general.logo')" />` shows `<x-fwb>`-styled progress during `livewire-upload-*` events and locks other `<x-ui.button>`s via Alpine `$store.ui.busy`.

### Buttons & Actions
*   **REQUIRED action button:** Always use `<x-ui.button>` (wraps `<x-fwb.button>`). It shows spinner + `__('general.working')` while the Livewire request runs, and `wire:loading.attr="disabled"` so sibling buttons cannot be clicked until the request finishes. During file upload, `$store.ui.busy` also disables all `<x-ui.button>`s.
*   Pass `target="methodName"` to scope the loading label (e.g. `target="save"`, `target="delete"`).
*   Secondary / cancel buttons that must not show the working label: `:loading="false"` (they still disable while busy).
*   Color mapping via `color` prop on `<x-ui.button>` / `<x-fwb.button>`:
    *   Save / Create / Import → `color="green"`
    *   Edit / primary brand → default / `color="blue"` (brand)
    *   Delete → `color="red"`
    *   Neutral / cancel → `color="light"` + `outline`
*   **One page action:** single `<x-ui.button>`.
*   **Multiple page actions:** `<x-fwb.dropdown>` to avoid mobile overflow.
*   **Tooltips (REQUIRED):** Always use `<x-fwb.tooltip>` with `<x-slot:triggerSlot>`. Do **not** hand-roll `data-tooltip-target` markup when wrapping interactive triggers.
    ```html
    <x-fwb.tooltip :id="'tooltip-edit-'.$user->id" placement="top">
        <x-slot:triggerSlot>
            <x-ui.button type="button" size="xs" wire:click="...">
                <x-lucide-pencil class="w-4 h-4" />
            </x-ui.button>
        </x-slot:triggerSlot>
        {{ __('general.edit') }}
    </x-fwb.tooltip>
    ```
*   **Row actions (PowerGrid):** `actionsFromView()` + `components.powergrid.row-actions` (`<x-fwb.tooltip>` + Lucide icon buttons).
*   In forms/modals only use full-width primary save buttons (`class="w-full"` on `<x-ui.button>`).

### Data Display
*   Prefer `<x-fwb.alert>` for record summaries inside modals; Lucide icons in the default/icon slot when needed.
## 7. Localization & Permissions (STRICT RULES)
*   **General Translations:** ALL UI texts, actions, and general words MUST be translated using ONLY the `general.php` file (e.g., `{{ __('general.create_user') }}` or `{{ __('general.save') }}`). Do NOT use other files for standard interface texts.
*   **Permissions List:** Use Spatie Laravel Permission v6. The COMPLETE list of permissions MUST be stored strictly inside `/lang/fa/permissions.php` and `/lang/en/permissions.php`.
*   **Permissions Structure (Role-Based):** Inside `permissions.php`, group permissions by roles as nested arrays:
    ```php
    return [
        'administrator' => [
            'user_create' => 'Create User',
            'user_edit' => 'Edit User',
        ],
        'manager' => [
            // ...
        ],
    ];
    ```

## 8. Reference Examples

**Page with PowerGrid table:**
```html
<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-heading">{{ __('general.users') }}</h1>

            <x-ui.button
                type="button"
                color="green"
                :loading="false"
                x-modal:open="{ modal: 'user.create' }"
            >
                <x-slot:icon>
                    <x-lucide-plus class="w-4 h-4 me-2" />
                </x-slot:icon>
                {{ __('general.create_user') }}
            </x-ui.button>
        </div>

        <x-fwb.card>
            <livewire:user-table :key="'user-table'" />
        </x-fwb.card>
    </div>
</div>
```

**PowerGrid table component (`app/Livewire/UserTable.php`) — searchable + relationSearch + actionsFromView:**
```php
<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class UserTable extends PowerGridComponent
{
    public string $tableName = 'usersTable';

    public function setUp(): array
    {
        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(config('main.per_page'))
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::query()->with(['role']); // eager-load searchable relations
    }

    /**
     * REQUIRED when searching related columns.
     * Maps relation name => searchable DB columns on that relation.
     */
    public function relationSearch(): array
    {
        return [
            'role' => [
                'name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('first_name')
            ->add('email')
            ->add('role_name', fn (User $user) => e($user->role?->name));
    }

    public function columns(): array
    {
        return [
            Column::make(__('general.first_name'), 'first_name')
                ->searchable()
                ->sortable(),

            Column::make(__('general.email'), 'email')
                ->searchable()
                ->sortable(),

            // Related column: searchable() + matching relationSearch() entry
            Column::make(__('general.role'), 'role_name')
                ->searchable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(User $row): View
    {
        return view('components.powergrid.row-actions', [
            'row' => $row,
            'editModal' => 'user.edit',
            'deleteModal' => 'user.delete',
            'idProp' => 'userId',
        ]);
    }
}
```

## 9. UI & CRUD Interaction Workflow
*   **Create & Edit:** Always via `elegantly/livewire-modal` **slideover** with direction-aware `position` (`rtl` → `end`, `ltr` → `start`). After success: `Toaster::success(...)`, `$this->dispatch('modal-close')`, and refresh the PowerGrid table.
*   **Delete:** Always via centered confirmation modal. After success: toast + close modal + refresh PowerGrid.
*   **PowerGrid refresh:** Dispatch `pg:eventRefresh-{tableName}` matching the table's `$tableName` property, e.g. `$this->dispatch('pg:eventRefresh-usersTable');`. Do not use generic names like `refresh-data`.
*   **PowerGrid row actions:** Use `actionsFromView()` + `components.powergrid.row-actions` (Flowbite `<x-fwb.tooltip>`). Do not use `Button::tooltip()` for styled tooltips.

## 10. Quick Package Cheatsheet
| Concern | Package / Tool | API |
|---|---|---|
| UI kit | Flowbite + `themesberg/flowbite-laravel-components` (aliqasemzadeh VCS) | `<x-fwb.*>`, data attributes + `initFlowbite()` |
| Forms | `x-fwb.input` / `textarea` / `select` / `checkbox` / `toggle` / … | [Forms docs](https://github.com/themesberg/flowbite-laravel-components#forms) |
| Action buttons | `<x-ui.button>` (wraps `x-fwb.button`) | loading label + disables siblings until done |
| File upload | `<x-ui.file-input>` (wraps `x-fwb.file-input`) | Livewire progress bar + `$store.ui.busy` |
| Tooltips | `<x-fwb.tooltip>` + `triggerSlot` | never hand-roll `data-tooltip-target` for triggers |
| Tables | `power-components/livewire-powergrid` + `App\Support\PowerGrid\FlowbiteTheme` | `PowerGridComponent`, `Column::make()->searchable()`, `relationSearch()`, `actionsFromView()` + `components.powergrid.row-actions` |
| Icons | `mallardduck/blade-lucide-icons` | `<x-lucide-{name} class="w-4 h-4" />` (in Blade views / actionsFromView) |
| Modal / Slideover | `elegantly/livewire-modal` (+ published `start`/`end`) | `position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"`, `x-modal:open`, `modal-open` / `modal-close` |
| Toast | `masmerise/livewire-toaster` (Flowbite-styled hub) | `Toaster::success()`, `<x-toaster-hub />` |
| Dates | `morilog/jalali` | Jalali formatting/parsing everywhere |
| Permissions | `spatie/laravel-permission` v6 | `/lang/{fa,en}/permissions.php` |
| Per page | `config/main.php` | `config('main.per_page')` |
