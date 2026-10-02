# Daily Wins Tracker

A small portfolio project for recording daily victories, built with a Laravel REST API and a Vue 3 frontend. Local development uses SQLite.

The project is built one iteration per commit, following [PLAN.md](PLAN.md). It currently includes the application scaffolds, `GET /api/health`, and the wins/categories models, migrations and demo data. The CRUD API and dashboard will follow in later iterations.

## Structure

```text
daily-wins/
├── backend/     # Laravel API
├── frontend/    # Vue 3 + Vite, Pinia and Axios
└── PLAN.md      # Scope, API contract and commit sequence
```

## Requirements

- PHP 8.5 with PDO SQLite enabled.
- Composer 2.
- Node.js 24 and npm.

## Run locally

In a terminal, starting from the repository root:

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Laravel uses `DB_CONNECTION=sqlite` and defaults to `backend/database/database.sqlite`. The local `.env`, application key and database are excluded from Git. When restarting an existing installation, only `php artisan serve` is needed.

Seeding creates seven categories and eight sample wins dated relative to the first seed run. Run `php artisan db:seed` from `backend/` to load them into an existing database; repeated runs preserve existing entries and do not duplicate the samples.

In a second terminal, starting from the repository root:

```bash
cd frontend
npm ci
npm run dev
```

Open the Vue application at <http://localhost:5173>.

Verify the API:

```bash
curl -i http://localhost:8000/api/health
```

Expected result: HTTP 200 with `{"status":"ok"}`.

## Checks

Run each command from its application directory:

```bash
# backend/
php artisan test
vendor/bin/pint --test

# frontend/
npm run build
```
