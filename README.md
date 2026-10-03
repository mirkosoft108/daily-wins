# Daily Wins Tracker

A small portfolio project for recording daily victories, built with a Laravel REST API and a Vue 3 frontend. Local development uses SQLite.

The project is built one iteration per commit, following [PLAN.md](PLAN.md). It includes a Laravel REST API with CRUD, search, category filtering and statistics, plus a responsive Vue dashboard for creating, editing and deleting wins.

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
cp .env.example .env
npm run dev
```

Open the Vue application at <http://localhost:5173>.

`VITE_API_BASE_URL` sets the frontend's API URL and defaults to `http://localhost:8000/api`. Restart Vite after changing it. For a production build, set it to the deployed API URL before running `npm run build`.

The dashboard loads wins, categories and statistics separately. Search is sent to the API after a short typing pause; changing category applies immediately. Statistics describe all wins and do not change when filtering the list. Loading, empty and error states include a way to retry failed requests.

Use **Add Win** to record a title, optional description, category and date. **Edit** reuses the same form; **Delete** asks for confirmation. Forms show required-field feedback and Laravel validation errors, preserve input after failures, and disable controls while saving. Successful changes reload the current filtered list and global statistics, then show a success message. A new or edited win may be hidden by the active filters. Dialogs support keyboard navigation and Escape to cancel when idle.

To check the dashboard manually:

- Open it at 320, 375, 768 and desktop widths and check for horizontal scrolling.
- Search for `gym`, choose `Health`, and clear the filters.
- Search for a term with no matches to see the empty state.
- Add a temporary win, edit its title/category/date, then cancel and confirm its deletion. Check the success messages and statistics after each change.
- Submit a blank title or category to see validation. Stop Laravel while a form is open, submit it, and check that the input remains available to retry after restarting Laravel.
- Use the browser's Network throttling to see loading states.
- Stop Laravel to see the error state, restart it, and use **Try again**.

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
| GET | `/api/stats` | Total wins, wins this week and current streak |
| GET | `/api/wins` | Wins, newest win date and then creation time first |
| POST | `/api/wins` | Create a win, HTTP 201 |
| GET | `/api/wins/{win}` | Read a win |
| PUT | `/api/wins/{win}` | Update a win |
| DELETE | `/api/wins/{win}` | Delete a win, HTTP 204 |

Categories return a JSON array. Win responses use a `data` key and include the category's `id`, `name` and `slug`, with `win_date` formatted as `YYYY-MM-DD`.

`GET /api/stats` returns a JSON object with integer `total_wins`, `wins_this_week` and `current_streak` fields. The total counts all wins. This week counts `win_date` values from Monday through Sunday of the current calendar week. The streak counts distinct consecutive days ending today, or yesterday if today has no win; a missing day breaks the streak, and future dates do not extend it. Calendar boundaries use Laravel's application timezone, currently UTC.

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
