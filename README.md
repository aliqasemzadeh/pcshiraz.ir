# پی سی شیراز (pcshiraz.ir)

E-commerce platform for selling computer parts and related products — built for the Iranian market with Persian (RTL) UI, Jalali dates, mobile OTP login, and local payment gateways.

## Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.4, Laravel 13 |
| UI | Livewire 4 (single-file components), Flowbite Blade (`x-fwb.*`), Tailwind CSS 4, Alpine.js |
| Tables | Livewire PowerGrid v6 |
| Modals | `elegantly/livewire-modal` (slideovers for create/edit) |
| Toasts | `masmerise/livewire-toaster` (Flowbite-styled hub) |
| Icons | Lucide via `mallardduck/blade-lucide-icons` |
| Auth | Mobile OTP (`spatie/laravel-one-time-passwords`) |
| Access control | Spatie Permission |
| Catalog / media | Spatie Media Library, Tags, Sluggable |
| Payments | Shetabit Payment (Iranian gateways) |
| Dates | Morilog Jalali |
| Validation | Persian validation helpers |
| Frontend build | Vite 8, Vazirmatn |

## Features

- **Shop storefront** — home, profile, category browsing, product search
- **Role-based panels**
  - Administrator — users, roles, permissions
  - Sale — brands, categories, items (catalog)
  - Colleague — colleague dashboard
  - Organization — organization / partner dashboard
- **Catalog** — brands, categories, items, prices, media, tags
- **Bilingual** — Persian (`fa`, RTL) and English (`en`) via `/lang`
- **Toasts & modals** — slideover create/edit, centered delete confirm, toaster feedback after actions

## Requirements

- PHP 8.4
- Composer 2
- Node.js 20+ and npm
- MySQL 8+ (default) or another Laravel-supported database

## Quick start

```bash
# Clone
git clone https://github.com/aliqasemzadeh/pcshiraz.ir.git
cd pcshiraz.ir

# One-shot setup (deps, .env, key, migrate+seed, npm build)
composer setup
```

Or step by step:

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configure DB_* (and optional SMS / payment keys) in .env
php artisan migrate --seed

npm install
npm run build
```

### Development servers

```bash
composer dev
```

Runs in parallel: `artisan serve`, queue worker, log viewer (`pail`), and Vite.

Or separately:

```bash
php artisan serve
npm run dev
php artisan queue:listen
```

App URL defaults to `http://localhost` (see `APP_URL` in `.env`).

## Demo accounts

After seeding:

| Mobile | Password | Notes |
| --- | --- | --- |
| `09121111111` | `password` | Base test user |
| `09120000000` | `password` | Demo shop owner (`demo@pcshiraz.ir`) |

Demo data includes sample PC categories, brands, and items.

Login uses **mobile OTP** in the UI; seeded passwords support non-OTP flows if enabled.

## Project structure (high level)

```
app/
  Models/           # Item, Brand, Category, User, …
  Livewire/         # PowerGrid *Table components (class-based)
  Livewire/Forms/   # Livewire form objects
  Services/Shop/    # Shop domain services
resources/views/
  pages/            # Livewire SCF pages (pages::…)
    auth/
    shop/
    panels/administrator|sale|colleague|organization/
  components/       # Shared / modal SFCs
  layouts/          # App layouts
lang/
  fa/ en/           # Translations (UI strings → general.php)
routes/web.php      # Livewire page routes
```

Livewire pages are registered as `pages::…`, for example:

- `pages::shop.home.index` → `/`
- `pages::auth.login` → `/login`
- `pages::panels.administrator.user.index` → `/administrator/users`
- `pages::panels.sale.catalog.item.index` → `/sale/catalog/items`

## Conventions

- Prefer **single-file Livewire components** under `resources/views/pages` (and modals under `components/`)
- **PowerGrid tables** are the only class-based Livewire exception — place under `app/Livewire/` with a `*Table` suffix
- Create / edit forms in **slideover modals** (`elegantly/livewire-modal`) with direction-aware side (`rtl` → `end`, `ltr` → `start`); delete uses a **centered** confirmation modal
- Index lists use **PowerGrid** (header search + footer pagination) — do not hand-roll `<table>` CRUD lists
- PowerGrid action icons must use `Blade::render('<x-lucide-… />')` (slots are injected via Alpine `x-html` and do not compile Blade tags)
- After Livewire actions, show feedback with **`Toaster::success(...)`** (`masmerise/livewire-toaster`, Flowbite-styled hub)
- Prefer **`<x-fwb.*>`** for presentational UI; keep PowerGrid / livewire-modal / existing layouts for CRUD shell
- New UI copy goes in **`lang/fa/general.php`** (and `lang/en/general.php` when needed)
- Permissions live in **`lang/fa/permissions.php`** and **`lang/en/permissions.php`**
- Dates shown to users use **Jalali** (`morilog/jalali`)
- Icons: Lucide Blade components (`<x-lucide-pencil class="w-4 h-4" />`)
- UI widgets (dropdown, tooltip, datepicker): **Flowbite** + `initFlowbite()`; prefer `x-fwb.*` where available
- Event names should be fully qualified (e.g. `panels.sale.catalog.item.edit.assign-data`)
- Do **not** use Flux UI (`flux:*`, `Flux::toast`, `php artisan flux:icon`)
- Pagination size: `config('main.per_page')`

Full coding rules: [`.junie/guidelines.md`](.junie/guidelines.md).

## Useful commands

```bash
php artisan migrate --seed      # Reset schema + demo data (destructive if fresh)
php artisan test                # PHPUnit
vendor/bin/pint                 # Code style
npm run build                   # Production assets
php artisan powergrid:create    # Scaffold a PowerGrid table component
php artisan livewire:form       # Scaffold a Livewire form object
```

## Environment notes

Key `.env` values:

| Variable | Purpose |
| --- | --- |
| `APP_URL` | Used when resolving the current shop domain host |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | `fa` / `en` |
| `DB_*` | MySQL connection (`pcshiraz` by default in `.env.example`) |
| `QUEUE_CONNECTION` | `database` by default — run a queue worker in dev |
| SMS / payment keys | Configure in `config/sms.php` and `config/payment.php` as needed |

## Panels & routes

| Prefix | Name | Purpose |
| --- | --- | --- |
| `/` | Shop | Public storefront |
| `/login` | Auth | OTP login |
| `/administrator/*` | Admin panel | Users, roles, permissions, domains |
| `/sale/*` | Sale panel | Catalog (brands, categories, items) |
| `/colleague/*` | Colleague panel | Colleague tools |
| `/organization/*` | Organization panel | Partner / org tools |

## License

Private / proprietary unless otherwise stated by the repository owner. Laravel framework components remain under the [MIT license](https://opensource.org/licenses/MIT).
