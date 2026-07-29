# پی سی شیراز (pcshiraz.ir)

Multi-domain e-commerce platform for selling computer parts and related products — built for the Iranian market with Persian (RTL) UI, Jalali dates, mobile OTP login, and local payment gateways.

## Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13 |
| UI | Livewire 4 (single-file components), Flux UI, Tailwind CSS 4, Alpine.js |
| Auth | Mobile OTP (`spatie/laravel-one-time-passwords`) |
| Access control | Spatie Permission |
| Catalog / media | Spatie Media Library, Tags, Sluggable |
| Payments | Shetabit Payment (Iranian gateways) |
| Subscriptions | Subscriptionify |
| Dates | Morilog Jalali |
| Validation | Persian validation helpers |
| Frontend build | Vite 8, Vazirmatn |

## Features

- **Shop storefront** — home, profile, category browsing, product search
- **Multi-domain** — each shop lives under its own domain with isolated catalog data
- **Role-based panels**
  - Administrator — users, roles, permissions, domains
  - Sale — brands, categories, items (catalog)
  - Colleague — colleague dashboard
  - Organization — organization / partner dashboard
- **Catalog** — brands, categories, items, prices, inventory, media, tags
- **Bilingual** — Persian (`fa`, RTL) and English (`en`) via `/lang`
- **Toasts & modals** — Flux flyout modals, Livewire toaster feedback after actions

## Requirements

- PHP 8.3+ (8.4 recommended)
- Composer 2
- Node.js 20+ and npm
- MySQL 8+ (default) or another Laravel-supported database
- Flux UI Pro credentials (private Composer repo: `https://composer.fluxui.dev`)

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

Demo data includes a domain titled **پی سی شیراز** with sample PC categories, brands, and items.

Login uses **mobile OTP** in the UI; seeded passwords support non-OTP flows if enabled.

## Project structure (high level)

```
app/
  Models/           # Domain, Item, Brand, Category, User, …
  Livewire/Forms/   # Livewire form objects
  Services/Shop/    # Shop domain services
resources/views/
  pages/            # Livewire SCF pages (pages::…)
    auth/
    shop/
    panels/administrator|sale|colleague|organization/
  layouts/          # App layouts
lang/
  fa/ en/           # Translations (add UI strings here)
routes/web.php      # Livewire page routes
```

Livewire pages are registered as `pages::…`, for example:

- `pages::shop.home.index` → `/`
- `pages::auth.login` → `/login`
- `pages::panels.administrator.user.index` → `/administrator/users`
- `pages::panels.sale.catalog.item.index` → `/sale/catalog/items`

## Conventions

- Prefer **single-file Livewire components** under `resources/views/pages`
- Create / edit forms in **Flux flyout modals** (`<flux:modal flyout position="right">`)
- Lists use **pagination** and searchable Flux tables
- After Livewire actions, show feedback with **`Flux::toast(...)`**
- New UI copy goes in **`lang/fa/…`** (and `lang/en/…` when needed)
- Dates shown to users use **Jalali** (`morilog/jalali`)
- Icons: Lucide via Flux (`php artisan flux:icon {name}`)
- Database selects with many options: Flux **backend-search** select
- Event names should be fully qualified (e.g. `panels.sale.catalog.item.edit.assign-data`)

## Useful commands

```bash
php artisan migrate --seed   # Reset schema + demo data (destructive if fresh)
php artisan test             # PHPUnit
vendor/bin/pint              # Code style
npm run build                # Production assets
php artisan flux:icon home   # Add a Lucide icon component
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
