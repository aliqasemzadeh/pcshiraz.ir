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
        <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
            {{ __('general.create_user') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            {{-- fields --}}

            <button type="submit" class="w-full text-white bg-teal-700 hover:bg-teal-800 focus:ring-4 focus:ring-teal-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-teal-600 dark:hover:bg-teal-700 focus:outline-none dark:focus:ring-teal-800">
                {{ __('general.save') }}
            </button>
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
        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
            {{ __('general.delete_confirmation') }}
        </h3>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            {{ __('general.delete_warning_message') }}
            <br>
            {{ __('general.action_cannot_be_reversed') }}
        </p>
        <div class="flex justify-end gap-2">
            <button type="button" x-modal:close class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:ring-4 focus:ring-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                {{ __('general.cancel') }}
            </button>
            <button type="button" wire:click="delete" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800">
                {{ __('general.delete') }}
            </button>
        </div>
    </x-livewire-modal::modal>
</x-livewire-modal::stack>
```

### Forms & Inputs (Flowbite)
*   Use Flowbite form control classes (`bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5`).
*   Always show labels and `@error` / Livewire error messages under fields.
*   **Numbers:** `<input type="number" ...>`.
*   **Selects:** Prefer searchable selects (Flowbite dropdown+search pattern or Select2-like Flowbite examples). For DB-driven options, load via Livewire computed properties / backend search.
*   **Multi-select:** Use Flowbite multi-select / checkbox dropdown patterns.
*   **Dates:** Use Flowbite Datepicker (`datepicker` data attributes) with Jalali display via `morilog/jalali` on the backend/format layer. All dates must be Jalali.
*   **Booleans / switches:** Use Flowbite toggle switch markup with `wire:model` (not `.live` unless requested).
*   **File uploads:** Use Flowbite file input styles. Inside slideovers/modals keep uploads compact/inline.

### Buttons & Actions
*   Use Flowbite button class sets. Map actions to colors:
    *   Save / Create / Import → teal or green (`bg-teal-700` / `bg-green-700`)
    *   Edit → blue (`bg-blue-700`)
    *   Delete → red (`bg-red-700`)
    *   Neutral / secondary → gray/light alternative button classes
*   **One page action:** single button.
*   **Multiple page actions:** Flowbite dropdown menu to avoid mobile overflow.
*   **Row actions:** icon-only small buttons with Lucide icons + Flowbite tooltip. For PowerGrid tables use `actionsFromView()` + `components.powergrid.row-actions` (`<x-fwb.tooltip>`). For non-PowerGrid Blade lists (rare), use `data-tooltip-target` + tooltip div:
    ```html
    <button
        type="button"
        data-tooltip-target="tooltip-edit-{{ $user->id }}"
        wire:click="$dispatch('panels.administrator.user.edit.assign-data', { user: {{ $user->id }} })"
        class="inline-flex items-center justify-center p-2 text-white bg-brand border border-transparent rounded-base shadow-xs hover:bg-brand-strong focus:outline-none focus:ring-4 focus:ring-brand-medium"
    >
        <x-lucide-pencil class="w-4 h-4" />
        <span class="sr-only">{{ __('general.edit') }}</span>
    </button>
    <div id="tooltip-edit-{{ $user->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-dark rounded-lg shadow-sm opacity-0 tooltip">
        {{ __('general.edit') }}
        <div class="tooltip-arrow" data-popper-arrow></div>
    </div>
    ```
*   In forms/modals only use full-width primary save buttons.

### Data Display
*   Use Flowbite Alert / Callout-style boxes to display record summaries (permissions, roles, users) inside modals:
    ```html
    <div class="flex items-center p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
        <x-lucide-info class="shrink-0 w-4 h-4 me-2" />
        <div>...</div>
    </div>
    ```

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
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('general.users') }}</h1>

            <button
                type="button"
                x-modal:open="{ modal: 'user.create' }"
                class="inline-flex items-center gap-2 text-white bg-teal-700 hover:bg-teal-800 focus:ring-4 focus:ring-teal-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-teal-600 dark:hover:bg-teal-700 focus:outline-none dark:focus:ring-teal-800"
            >
                <x-lucide-plus class="w-4 h-4" />
                {{ __('general.create_user') }}
            </button>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 p-4 sm:p-6">
            <livewire:user-table :key="'user-table'" />
        </div>
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
| Tables | `power-components/livewire-powergrid` + `App\Support\PowerGrid\FlowbiteTheme` | `PowerGridComponent`, `Column::make()->searchable()`, `relationSearch()`, `actionsFromView()` + `components.powergrid.row-actions` |
| Icons | `mallardduck/blade-lucide-icons` | `<x-lucide-{name} class="w-4 h-4" />` (in Blade views / actionsFromView) |
| Modal / Slideover | `elegantly/livewire-modal` (+ published `start`/`end`) | `position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"`, `x-modal:open`, `modal-open` / `modal-close` |
| Toast | `masmerise/livewire-toaster` (Flowbite-styled hub) | `Toaster::success()`, `<x-toaster-hub />` |
| Dates | `morilog/jalali` | Jalali formatting/parsing everywhere |
| Permissions | `spatie/laravel-permission` v6 | `/lang/{fa,en}/permissions.php` |
| Per page | `config/main.php` | `config('main.per_page')` |
