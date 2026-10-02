# Daily Wins Tracker

A small portfolio project for recording daily victories, built with a Laravel REST API and a Vue 3 frontend. Local development uses SQLite.

The project is built one iteration per commit, following [PLAN.md](PLAN.md). It currently includes the application scaffolds, the wins/categories domain and demo data, and a REST API with CRUD, search and category filtering. Statistics and the Vue dashboard will follow in later iterations.

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

## API

| Method | Endpoint | Result |
| --- | --- | --- |
| GET | `/api/health` | Health status |
| GET | `/api/categories` | Category list |
| GET | `/api/wins` | Wins, newest win date and then creation time first |
| POST | `/api/wins` | Create a win, HTTP 201 |
| GET | `/api/wins/{win}` | Read a win |
| PUT | `/api/wins/{win}` | Update a win |
| DELETE | `/api/wins/{win}` | Delete a win, HTTP 204 |

Categories return a JSON array. Win responses use a `data` key and include the category's `id`, `name` and `slug`, with `win_date` formatted as `YYYY-MM-DD`.

Create and update require `title` (at most 120 characters), an existing integer `category_id`, and a valid `win_date` in `YYYY-MM-DD` format. `description` is optional; send `null` to clear it when updating. Validation errors return JSON with HTTP 422; missing wins return JSON with HTTP 404.

Search titles and descriptions and filter by category slug:

```bash
curl 'http://localhost:8000/api/wins?search=gym&category=health'
```

Example create request (use an ID from `/api/categories`):

```bash
curl -i -X POST http://localhost:8000/api/wins \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"title":"Finished an exercise","description":null,"category_id":4,"win_date":"2026-10-02"}'
```

## Checks

Run each command from its application directory:

```bash
# backend/
php artisan test
vendor/bin/pint --test

# frontend/
npm run build
```
