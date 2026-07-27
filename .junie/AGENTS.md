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
*   **Icons:** Lucide via `mallardduck/blade-lucide-icons` (https://lucide.dev/icons). Use Blade components: `<x-lucide-pencil class="w-4 h-4" />`. Icon names use kebab-case matching Lucide (e.g. `pencil`, `trash-2`, `plus`, `search`, `chevron-down`).
*   **Modals:** `elegantly/livewire-modal` (https://elegantly.dev/livewire-modal) — NOT Flowbite modals/drawers for CRUD forms.
*   **Toasts:** `masmerise/livewire-toaster` — use `Masmerise\Toaster\Toaster` facade.
*   **Do NOT use FluxUI** (`flux:*` components, `Flux::toast`, `Flux::modal`, `php artisan flux:icon`).

## 2. Architecture & File Structure
*   **Single-File Component Architecture:** ALWAYS write Livewire components as Single-File Components. Place all PHP logic inside a `<?php ... ?>` block at the top of the `.blade.php` file.
*   **File Location:** Save these components directly in `resources/views/components/` or `resources/views/pages/`.
*   **Root Node:** Ensure the HTML portion of the component always has a single root wrapping element.
*   **Separation of Concerns:** Use AlpineJS for UI manipulation and Livewire strictly for Backend logic.
*   **Livewire Inclusions:** When loading a Livewire component inside a view, always pass a key: `<livewire:component-name :key="$componentId" />`.
*   **Pages namespace:** For Livewire pages use `pages::` and SCF (single-file components). Do not convert to class-based components.

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

Ensure Tailwind content / `@source` includes:
*   `./vendor/elegantly/livewire-modal/resources/views/**/*.blade.php`
*   `./vendor/masmerise/livewire-toaster/resources/views/*.blade.php`
*   Flowbite paths as required by the Flowbite Laravel/Tailwind setup

Initialize Flowbite for interactive widgets (dropdown, tooltip, datepicker, etc.) after Livewire navigations when needed (`initFlowbite()`).

## 6. Flowbite Component Rules & UI Innovation
*   **Innovative UI/UX:** Build modern, clean UIs with Flowbite patterns + Tailwind utility classes (smart empty states, loading transitions, clean alignment, modern spacing).
*   Prefer Flowbite HTML/class patterns from https://flowbite.com/ and the llms-full.txt guide. Do not invent a parallel component API.

### Layout & Pages
*   **Page Titles:** Use `<x-slot name="title">Page Title - {{ config('app.name') }}</x-slot>`.
*   **Breadcrumbs:** Always include Flowbite breadcrumb markup (`nav` + `ol` with `inline-flex items-center`).
*   **Cards:** Wrap search/filter and table blocks in Flowbite card containers (`bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 p-4 sm:p-6`).

### Tables & Lists
*   Use Flowbite table markup (`relative overflow-x-auto`, `w-full text-sm text-left`, `thead` with `text-xs text-gray-700 uppercase bg-gray-50`).
*   Implement pagination using `->paginate(config('general.per_page'))` and Laravel pagination links styled with Flowbite pagination classes.
*   Put search/filter above the table inside the card. Example search input:
    ```html
    <div class="relative mb-4">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <x-lucide-search class="w-4 h-4 text-gray-500" />
        </div>
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
            placeholder="{{ __('general.search') }}..."
        />
    </div>
    ```

### Modals (`elegantly/livewire-modal`)
*   **Create / Edit:** Always use **slideover** (right-side panel). Never redirect to separate create/edit routes.
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
    <x-livewire-modal::slideover class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800">
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
*   **Row actions:** icon-only small buttons with Lucide icons + Flowbite tooltip (`data-tooltip-target`):
    ```html
    <button
        type="button"
        data-tooltip-target="tooltip-edit-{{ $user->id }}"
        wire:click="$dispatch('panels.administrator.user.edit.assign-data', { user: {{ $user->id }} })"
        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm p-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
    >
        <x-lucide-pencil class="w-4 h-4" />
        <span class="sr-only">{{ __('general.edit') }}</span>
    </button>
    <div id="tooltip-edit-{{ $user->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
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

**Table Example:**
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
            <div class="relative mb-4">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <x-lucide-search class="w-4 h-4 text-gray-500" />
                </div>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                    placeholder="{{ __('general.search') }}..."
                />
            </div>

            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">{{ __('general.first_name') }}</th>
                            <th scope="col" class="px-6 py-3 text-end">{{ __('general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->users as $user)
                            <tr wire:key="user-{{ $user->id }}" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                <td class="px-6 py-4">{{ $user->first_name }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            data-tooltip-target="tooltip-edit-{{ $user->id }}"
                                            wire:click="$dispatch('panels.administrator.user.edit.assign-data', { user: {{ $user->id }} })"
                                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm p-2"
                                        >
                                            <x-lucide-pencil class="w-4 h-4" />
                                        </button>
                                        <div id="tooltip-edit-{{ $user->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                            {{ __('general.edit') }}
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>

                                        <button
                                            type="button"
                                            data-tooltip-target="tooltip-delete-{{ $user->id }}"
                                            x-modal:open="{ modal: 'user.delete', props: { userId: {{ $user->id }} } }"
                                            class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm p-2"
                                        >
                                            <x-lucide-trash-2 class="w-4 h-4" />
                                        </button>
                                        <div id="tooltip-delete-{{ $user->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                            {{ __('general.delete') }}
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $this->users->links() }}
            </div>
        </div>
    </div>
</div>
```

## 9. UI & CRUD Interaction Workflow
*   **Create & Edit:** Always via `elegantly/livewire-modal` **slideover**. After success: `Toaster::success(...)`, `$this->dispatch('modal-close')`, and dispatch the page table refresh event.
*   **Delete:** Always via centered confirmation modal. After success: toast + close modal + refresh event.
*   **Event-Driven Table Refresh:** Dispatch a context-specific event, e.g. `$this->dispatch('panels.administrator.user.index.table');`. The listing page listens with `#[On('panels.administrator.user.index.table')]`. Do not use generic names like `refresh-data`.

## 10. Quick Package Cheatsheet
| Concern | Package / Tool | API |
|---|---|---|
| UI kit | Flowbite | Tailwind classes + data attributes + `initFlowbite()` |
| Icons | `mallardduck/blade-lucide-icons` | `<x-lucide-{name} class="w-4 h-4" />` |
| Modal / Slideover | `elegantly/livewire-modal` | `x-modal:open`, `modal-open` / `modal-close`, `<x-livewire-modal::slideover>` |
| Toast | `masmerise/livewire-toaster` | `Toaster::success()`, `<x-toaster-hub />` |
| Dates | `morilog/jalali` | Jalali formatting/parsing everywhere |
| Permissions | `spatie/laravel-permission` v6 | `/lang/{fa,en}/permissions.php` |
