# Daily Wins Tracker

**Small progress matters.** A simple app for recording everyday victories: finishing an exercise, going to the gym, making time to learn, or starting a conversation.

Built with a Laravel REST API and a Vue 3 dashboard. The app supports creating, editing, deleting, searching and filtering wins, with a few useful progress statistics.

**Status:** the application works locally and includes production configuration for Neon, Render and Firebase Hosting. Follow the deployment guide below to publish it; verified live links and screenshots will be added afterward. This is a single-user portfolio demo with shared data and no authentication.

## Features

- Create and edit a win with a title, optional description, category and date.
- Delete a win after confirmation.
- Search titles and descriptions, combine search with a category filter, and clear filters.
- View total wins, wins this week and the current streak.
- See loading, empty, error and success states, with retries for failed requests.
- Keep form input after a failed save, show field validation, and prevent repeated submissions while saving.
- Use a responsive layout from 320px, labeled controls, visible keyboard focus and keyboard-accessible dialogs.

Categories: Career, Health, Social, Learning, Mindfulness, Personal and Other. Wins are ordered by date descending, then creation time descending. Dashboard statistics describe all wins, even while the list is filtered.

## Tech stack

| Layer | Tools |
| --- | --- |
| API | Laravel 13, PHP, Eloquent, Form Requests, JSON Resources |
| Frontend | Vue 3 Composition API, Vite 8 |
| State and HTTP | Pinia 4, Axios |
| Styling | Plain CSS, native HTML controls and dialogs |
| Local database | SQLite |
| Automated checks | PHPUnit 12 / Laravel Feature tests, Laravel Pint, Vite production build |
| Production database | PostgreSQL on Neon |
| Production runtime / hosting | PHP 8.4 with Apache in Docker on Render; Firebase Hosting for the SPA |

## Screenshots

Screenshots will be added after the public demo is deployed and verified.

| View | Screenshot status |
| --- | --- |
| Desktop dashboard | Pending |
| Mobile dashboard | Pending |
| Create / edit form | Pending |

## Architecture

The frontend and API are separate applications. Vue renders the interface; Pinia owns the shared state and API operations; Axios sends JSON requests to Laravel.

```mermaid
flowchart LR
    UI["Vue components"] --> Store["Pinia wins store"]
    Store --> HTTP["Axios client"]
    HTTP --> API["Laravel REST API"]
    API --> DB[("SQLite locally")]
```

Laravel routes resolve to controllers. `SaveWinRequest` validates create/update payloads before the controller writes through Eloquent. `WinResource` provides the response shape, including the category and calendar date. `WinStatsService` calculates the dashboard statistics separately from the controller.

A category has many wins; each win belongs to one category. Migrations define the tables and foreign key. Seeders provide categories and sample entries.

```text
daily-wins/
├── backend/
│   ├── app/Http/Controllers/   # REST endpoints
│   ├── app/Http/Requests/      # Create/update validation
│   ├── app/Http/Resources/     # Win JSON shape
│   ├── app/Models/            # Win and Category relationships
│   ├── app/Services/          # Statistics and streak calculation
│   ├── database/             # Migrations, factories and seeders
│   ├── Dockerfile            # Production PHP / Apache image
│   ├── docker/               # PORT binding and startup configuration
│   ├── routes/api.php
│   └── tests/                # API and domain tests
├── frontend/
│   ├── firebase.json         # Static hosting and SPA fallback
│   └── src/
│       ├── api/              # Configured Axios client
│       ├── components/       # Cards, filters, forms and dialogs
│       ├── stores/           # Pinia state and API operations
│       └── views/            # Dashboard orchestration
├── README.md
└── PLAN.md                   # Scope and one-iteration-per-commit workflow
```

## Local requirements

- Git.
- PHP **8.4.1 or newer** for the dependencies in `backend/composer.lock`; development was verified with PHP 8.5.11.
- PHP extensions required by Composer, plus `pdo_sqlite` for the local database. `composer check-platform-reqs` checks the installed packages' requirements.
- Composer 2.
- Node.js **22.12 or newer** and npm; development was verified with Node 24. Vite also supports Node 20.19 or newer within the 20.x release line.

SQLite is sufficient locally; no database server, Redis or queue worker is needed to use the tracker.

## Local installation

Clone the repository:

```bash
git clone https://github.com/mirkosoft108/daily-wins.git
cd daily-wins
```

### Backend

In the first terminal, starting from the repository root:

```bash
cd backend
composer install
composer check-platform-reqs
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

The API runs at <http://localhost:8000/api>. Check it with:

```bash
curl -i http://localhost:8000/api/health
```

Expected result: HTTP 200 with `{"status":"ok"}`. This endpoint confirms the API responds; it does not check the database connection.

Seeding creates seven categories and eight sample wins dated relative to the first seed run. `php artisan db:seed` can add the demo data to an existing installation. The seeders use `firstOrCreate`: unchanged sample entries are preserved, and their dates do not advance on subsequent runs.

### Frontend

In a second terminal, starting from the repository root:

```bash
cd frontend
npm ci
cp .env.example .env
npm run dev
```

Open <http://localhost:5173>. Keep both servers running. To restart an existing installation, run only `php artisan serve` in `backend/` and `npm run dev` in `frontend/`.

The frontend defaults to `http://localhost:8000/api`. If the API uses a different port, update `frontend/.env` and restart Vite.

## Environment variables

Copy the committed examples into each application's own `.env`. Local `.env` files, database files, dependencies and generated assets are excluded from Git.

### Backend — `backend/.env`

| Variable | Local value / purpose |
| --- | --- |
| `APP_NAME` | `Daily Wins Tracker` |
| `APP_ENV` | `local` |
| `APP_KEY` | Generated by `php artisan key:generate`; the example is intentionally blank |
| `APP_DEBUG` | `true` locally; use `false` in production |
| `APP_URL` | `http://localhost:8000` |
| `DB_CONNECTION` | `sqlite` locally |
| `DB_DATABASE` | Leave unset for `backend/database/database.sqlite`; an absolute path can select another SQLite file |
| `LOG_CHANNEL` / `LOG_LEVEL` | `stack` / `debug` locally |
| `SESSION_DRIVER` / `CACHE_STORE` / `QUEUE_CONNECTION` | Laravel's local `database` defaults; tracker requests do not require a queue worker |
| `FRONTEND_ORIGINS` | Comma-separated exact origins; local defaults allow Vite on localhost / 127.0.0.1 ports 5173 and 4173 |
| `TRUSTED_PROXIES` | Empty locally; `*` behind Render's reverse proxy |

The Laravel application timezone is **UTC**, defined in `backend/config/app.php`. Production settings have a separate, secret-free template: [backend/.env.production.example](backend/.env.production.example). Add the completed values to Render's environment settings; populated `.env.production` files stay ignored.

### Frontend — `frontend/.env`

| Variable | Local value / purpose |
| --- | --- |
| `VITE_API_BASE_URL` | `http://localhost:8000/api`, including the `/api` suffix |

Restart Vite after changing this value. For a production build, set it to the deployed API URL **before** running `npm run build`. Vite embeds these values in the browser bundle, so `VITE_` variables are public configuration.

## API

Base URL locally: `http://localhost:8000/api`. Requests and responses use JSON; clients should send `Accept: application/json` and use `Content-Type: application/json` for create/update requests.

| Method | Endpoint | Successful response |
| --- | --- | --- |
| GET | `/api/health` | 200, `{"status":"ok"}` |
| GET | `/api/categories` | 200, category array |
| GET | `/api/stats` | 200, statistics object |
| GET | `/api/wins` | 200, `{"data":[...]}` |
| POST | `/api/wins` | 201, `{"data":{...}}` |
| GET | `/api/wins/{win}` | 200, `{"data":{...}}` |
| PUT | `/api/wins/{win}` | 200, `{"data":{...}}` |
| DELETE | `/api/wins/{win}` | 204, empty body |

### Search and category filtering

`GET /api/wins` accepts optional `search` and `category` query parameters. Search matches titles and descriptions without case sensitivity; `category` is a category **slug**, such as `health`. Both filters can be combined:

```bash
curl 'http://localhost:8000/api/wins?search=gym&category=health'
```

Win responses include a nested category and a `YYYY-MM-DD` date:

```json
{
  "data": [
    {
      "id": 1,
      "title": "Went to the gym",
      "description": "Trained even though I felt tired.",
      "win_date": "2026-10-02",
      "category": { "id": 2, "name": "Health", "slug": "health" }
    }
  ]
}
```

The frontend waits 300ms after typing before searching, applies category changes immediately, and cancels superseded list requests.

### Create and update

Both POST and PUT require the same fields. Update sends the full form, including `description: null` to clear an existing description.

| Field | Validation |
| --- | --- |
| `title` | Required string, at most 120 characters |
| `description` | Optional nullable string |
| `category_id` | Required integer referring to an existing category |
| `win_date` | Required valid calendar date in `YYYY-MM-DD` format |

Example create request; use a category ID from `GET /api/categories`:

```bash
curl -i -X POST http://localhost:8000/api/wins \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"title":"Finished an exercise","description":null,"category_id":4,"win_date":"2026-10-02"}'
```

Invalid payloads return **422** with Laravel's `message` and field-specific `errors`. Missing win IDs return **404**. The Vue form displays the field messages and keeps the draft available for correction or retry.

### Statistics and dates

`GET /api/stats` returns integer values:

```json
{ "total_wins": 48, "wins_this_week": 12, "current_streak": 7 }
```

- **Total wins:** all recorded wins.
- **This week:** win dates from Monday through Sunday of the current UTC calendar week.
- **Current streak:** consecutive calendar days ending today if there is a win today, otherwise yesterday. A missing day breaks the streak; several wins on one day count once; future dates do not extend it.

The form defaults to the browser's local date. Cards preserve the recorded calendar date when formatting it, so a timezone offset does not shift it to the previous day. Statistics use the API's UTC calendar boundaries.

After a successful create, edit or delete, Pinia reloads the filtered list and global statistics. Active filters remain in place, so a saved win may be hidden by them. If a refresh fails after a successful write, the write is still reported as successful and each failed read can be retried separately.

## Tests and build

From `backend/`:

```bash
php artisan test
vendor/bin/pint --test
```

The backend suite covers relationships and seeding, CRUD, payload validation, missing IDs, ordering, combined filters, statistics, production database restrictions and CORS. `backend/phpunit.xml` selects an in-memory SQLite database by default. To run against PostgreSQL, override the database environment variables with a dedicated disposable test database; the suite resets its tables.

From `frontend/`:

```bash
npm run build
```

The output is written to `frontend/dist/` and is excluded from Git. To inspect the production bundle locally while Laravel is running:

```bash
npm run preview
```

Open the URL printed by Vite. The repository includes automated backend tests; use the following browser checks for the interface.

### Manual verification

1. Check the dashboard and form at 320, 375, 768 and desktop widths, including scrolling and long titles.
2. Search for `gym`, choose `Health`, clear the filters, and try a term with no matches.
3. Create a temporary win; edit its title, category, date and description; verify success feedback and statistics.
4. Open Delete, cancel once, then confirm. Check that the win disappears and the total updates.
5. Submit a blank title or category. Use Tab / Shift+Tab inside the dialog and Escape to cancel; check that focus returns to a dashboard control.
6. Use Network throttling to inspect loading states. Stop Laravel while a form is open, try saving, restart Laravel and retry with the retained input.

## Production deployment

```mermaid
flowchart LR
    Browser["Browser"] --> Hosting["Firebase Hosting: Vue SPA"]
    Browser -- "HTTPS / JSON" --> Render["Render: Laravel API"]
    Render --> Neon[("Neon: PostgreSQL")]
```

| Service | Responsibility |
| --- | --- |
| [Firebase Hosting](https://firebase.google.com/docs/hosting) | Serve the compiled Vue application |
| [Render](https://render.com/docs/free) | Run the Laravel API in a Docker web service |
| [Neon](https://neon.com/docs/manage/endpoints/) | Persist the PostgreSQL database |

You need GitHub, Neon, Render and Firebase accounts. Choose the free plans explicitly. The steps below are manual; repository preparation does not create services or publish the application. Replace all example domains and project IDs with your own values.

### 1. Create the Neon database

1. Sign in at [Neon](https://console.neon.tech), create a project named `daily-wins`, and choose a region close to your intended Render region.
2. Open the project's connection panel. Select the production branch, database and role. Keep the direct connection for this small demo.
3. Copy the PostgreSQL connection URI, including the TLS option, into a private place. Copy only the URI, without a surrounding `psql` command or quotes. Its structure is `postgresql://USER:PASSWORD@HOST/DATABASE?sslmode=require`; preserve the URI's password encoding.
4. Use this URI as **`DB_URL`** on Render. This Laravel configuration reads `DB_URL`; setting only `DATABASE_URL` does not configure the application.

Each Neon branch has its own connection information. See [Neon's connection workflow](https://neon.com/docs/get-started-with-neon/workflow-primer). Keep the password and complete URI out of Git, screenshots and shared logs.

#### Optional Neon CLI setup

The root [neon.ts](neon.ts) defines a Postgres-only policy. The root npm packages support this configuration; the Laravel API and Vue frontend have their own dependencies.

From the repository root, using your own Neon project ID:

```bash
npm ci
npm install -g neon@latest
neon login
neon link --project-id YOUR_PROJECT_ID --branch production -y
neon config plan
neon deploy
```

The empty policy uses the project's existing PostgreSQL database. `neon deploy` applies Neon service configuration; Laravel migrations run when the backend starts on Render in step 3. The link context `.neon` and pulled `.env.local` are ignored by Git. This Laravel app reads **`DB_URL`**: copy the private `DATABASE_URL_UNPOOLED` from `.env.local` into Render's `DB_URL` for the direct connection.

Agent skills installed with `neon skills -y` stay local and are also ignored. To configure MCP for Codex with access to your specific project, use `neon mcp -y --agent codex --project-id YOUR_PROJECT_ID`. See [Neon's CLI setup and config workflow](https://neon.com/blog/just-landed-in-the-neon-cli).

### 2. Create the Render backend

Ensure the production preparation commit is pushed to `main`. In the [Render dashboard](https://dashboard.render.com), create a **Web Service**, connect GitHub, and select `mirkosoft108/daily-wins`.

| Setting | Value |
| --- | --- |
| Name | An available name, for example `daily-wins-api` |
| Branch | `main` |
| Region | Close to the Neon project |
| Language / runtime | **Docker** |
| Root Directory | **`backend`** |
| Dockerfile Path | **`./Dockerfile`** |
| Docker Build Context | **`.`**, if the field is shown |
| Docker Command | Leave empty to use the image's startup command |
| Instance type | **Free** |
| Health Check Path | **`/api/health`** |

Docker paths are relative to the selected root directory. Render builds the image; there is no separate Composer build command or PHP start command to enter. See [Render's monorepo settings](https://render.com/docs/monorepo-support) and [Docker deployment settings](https://render.com/docs/docker).

Generate a production key locally, from `backend/`:

```bash
php artisan key:generate --show
```

Copy the result privately to Render's `APP_KEY`; this command does not change your local `.env`. Keep the same key across redeploys.

Before creating the service, add these environment variables in Render. Enter plain values in the dashboard, without dotenv quotes:

| Variable | Production value |
| --- | --- |
| `APP_NAME` | `Daily Wins Tracker` |
| `APP_ENV` | `production` |
| `APP_KEY` | The generated key |
| `APP_DEBUG` | `false` |
| `APP_URL` | Initially `https://example.invalid`; replace with the assigned `https://…onrender.com` URL after creation |
| `DB_CONNECTION` | `pgsql` |
| `DB_URL` | The private Neon PostgreSQL URI |
| `DB_SSLMODE` | `require` |
| `FRONTEND_ORIGINS` | Initially `https://example.invalid`; replace with your Firebase origins in step 4 |
| `TRUSTED_PROXIES` | `*`, for Render's reverse proxy |
| `LOG_CHANNEL` / `LOG_LEVEL` | `stderr` / `info` |
| `SESSION_DRIVER` / `CACHE_STORE` / `QUEUE_CONNECTION` | `array` / `file` / `sync` |
| `APP_MAINTENANCE_DRIVER` / `MAIL_MAILER` | `file` / `log` |
| `RUN_MIGRATIONS` | `true` |
| `SEED_DEMO` | `true` for the first deployment only |

Render provides `PORT`, normally `10000`. The entrypoint configures Apache to listen on that port on all interfaces. PHP uses a 128 MB memory limit and Apache runs at most two request workers. The image installs production Composer dependencies and `pdo_pgsql`; it excludes local configuration, databases, tests and generated caches. Configuration and routes are cached at startup using runtime variables. See [Render's port binding requirement](https://render.com/docs/web-services#port-binding).

As an alternative to `DB_URL`, leave it unset and provide `DB_HOST`, `DB_PORT=5432`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` and `DB_SSLMODE=require`. Keep `DB_CONNECTION=pgsql`. Production rejects SQLite, including a SQLite URI hidden in `DB_URL`.

### 3. Deploy, migrate and seed the backend

Click **Create Web Service** and watch the build/deploy logs. Startup performs these commands when the corresponding flags are enabled:

```bash
# RUN_MIGRATIONS=true
php artisan migrate --force --no-interaction

# SEED_DEMO=true
php artisan db:seed --force --no-interaction
```

Render Free has no remote shell or one-off jobs, so this project runs the initial migrations and seed through the container's entrypoint. A migration or seed failure stops startup instead of serving an incomplete installation. `migrate` applies pending migrations; never use `migrate:fresh` on the production database. See [Render Free limitations](https://render.com/docs/free).

When the service is live, replace the sample URL and check:

```bash
curl -i https://YOUR_SERVICE.onrender.com/api/health
curl https://YOUR_SERVICE.onrender.com/api/categories
curl https://YOUR_SERVICE.onrender.com/api/stats
```

Health should return HTTP 200 with `{"status":"ok"}`. Categories should contain seven entries; a fresh demo has eight wins and a current streak of five. Health does not probe PostgreSQL, so also check categories and wins. Update `APP_URL` to the assigned service URL if needed.

**Set `SEED_DEMO=false` and save/redeploy immediately after the initial seed.** The seeders preserve unchanged entries, but deleting or editing their identifying title/category can make a later seed recreate them. Keep `RUN_MIGRATIONS=true` to apply future pending migrations. Re-enable seeding only when intentionally restoring demo data.

### 4. Create Firebase Hosting and configure CORS

1. Create a Firebase project from the [Firebase console](https://console.firebase.google.com) and keep it on the **Spark** plan. Record its project ID, for example `YOUR_PROJECT_ID`. This app uses static Hosting; no Firebase SDK, Firestore or authentication setup is needed.
2. Its default Hosting origins will be `https://YOUR_PROJECT_ID.web.app` and `https://YOUR_PROJECT_ID.firebaseapp.com`.
3. In Render, replace the temporary `FRONTEND_ORIGINS` with these two exact origins, separated by a comma:

```text
https://YOUR_PROJECT_ID.web.app,https://YOUR_PROJECT_ID.firebaseapp.com
```

Save and redeploy the backend so the cached configuration is refreshed. Origins include the scheme and host, without a path or trailing slash. Add any future custom domain explicitly. Wildcards are discarded; production has no local-origin fallback. CORS allows JSON CRUD requests from the listed browser origins without credentials. The API remains a public, shared-data demo.

Install the Firebase CLI and sign in from your own terminal:

```bash
npm install -g firebase-tools
firebase login
firebase projects:list
```

Confirm your project appears. See the [Firebase CLI setup](https://firebase.google.com/docs/cli). The repository already contains `frontend/firebase.json`; you can deploy with `--project` without running `firebase init` or creating a committed `.firebaserc`.

### 5. Build and publish the frontend

From `frontend/`, create an ignored production environment file:

```bash
cp .env.example .env.production
```

Edit its API value to use your deployed backend, including **`/api`**:

```dotenv
VITE_API_BASE_URL=https://YOUR_SERVICE.onrender.com/api
```

Then build and deploy:

```bash
npm ci
npm run build
firebase deploy --only hosting --project YOUR_PROJECT_ID
```

Open the Hosting URL reported by the CLI. `firebase.json` serves `dist/` and rewrites unknown paths to `/index.html`, so reloading a SPA URL works. Hashed assets are cached; the index is revalidated. See [Firebase Hosting rewrites](https://firebase.google.com/docs/hosting/full-config#rewrites).

Changing `VITE_API_BASE_URL` requires another build and Hosting deploy. Backend environment changes require a Render redeploy. Never put a database password, `APP_KEY` or other secret in a `VITE_` variable.

### Local deployment checks

With Docker Desktop running, build the same Linux architecture used for the backend deployment, from the repository root:

```bash
docker build --platform linux/amd64 -t daily-wins-api ./backend
cp backend/.env.production.example backend/.env.production
```

Fill the blank values in `backend/.env.production` privately. Use a separate test PostgreSQL database when enabling migrations/seeding; those flags modify the selected database. Start the image with:

```bash
docker run --rm --env-file backend/.env.production \
  -p 127.0.0.1:10000:10000 daily-wins-api
```

Check `http://localhost:10000/api/health` and `/api/categories`. To use another container port, set `PORT` and map that same port. The image also accepts explicit Artisan commands, for example `docker run --rm --env-file backend/.env.production daily-wins-api php artisan migrate:status`.

To check Hosting locally after `npm run build`, from `frontend/`:

```bash
firebase emulators:start --only hosting --project demo-daily-wins
```

Open `http://localhost:5005` and a nested path such as `/preview/example`; both should serve the SPA. Port 5005 avoids macOS AirPlay's usual port 5000. To connect that local origin to your API, add `http://localhost:5005` to its `FRONTEND_ORIGINS` and restart it. The emulator does not publish anything.

### Deployment verification checklist

- [ ] Render is live with `APP_ENV=production`, `APP_DEBUG=false`, PostgreSQL and `SEED_DEMO=false` after initialization.
- [ ] `/api/health` returns 200; categories, wins and stats can read the Neon database.
- [ ] Firebase loads the dashboard over HTTPS and calls the correct HTTPS API URL.
- [ ] The production Origin receives its exact `Access-Control-Allow-Origin`; an unrelated Origin is never reflected or allowed by a wildcard.
- [ ] Create a temporary win, edit it, cancel deletion once, then delete it; confirm list and statistics refresh.
- [ ] Search, category filters, validation errors and retry controls work.
- [ ] Refresh a nested Hosting URL; the SPA still loads. Check mobile widths and keyboard navigation.
- [ ] Confirm test entries stay deleted across a backend redeploy and the database persists in Neon.
- [ ] Git contains no populated `.env`, connection URI, app key, SQLite database or generated build output.
- [ ] Record the verified live URLs and screenshots for the final portfolio commit in [PLAN.md](PLAN.md).

The free backend sleeps after 15 minutes without traffic; waking can take about a minute. The frontend's 60-second request timeout may expire during a cold start; wait for the service and use Retry. Render's filesystem is ephemeral, so PostgreSQL data lives in Neon rather than a local SQLite file. See [Render's free-service behavior](https://render.com/docs/free#spinning-down-on-idle).

For startup failures, check the required key/origins, PostgreSQL URI, TLS and migration logs. For browser CORS errors, compare the browser's exact Origin with `FRONTEND_ORIGINS`. For requests to localhost or a missing `/api` prefix, correct `VITE_API_BASE_URL`, rebuild and redeploy Hosting.

## What this project demonstrates

| Area | Concrete example |
| --- | --- |
| REST API design and validation | [Routes](backend/routes/api.php), [WinController](backend/app/Http/Controllers/WinController.php), [SaveWinRequest](backend/app/Http/Requests/SaveWinRequest.php) and [WinResource](backend/app/Http/Resources/WinResource.php) |
| Relational modeling | [Win](backend/app/Models/Win.php), [Category](backend/app/Models/Category.php), [migrations and seeders](backend/database) |
| Isolated domain logic | [WinStatsService](backend/app/Services/WinStatsService.php) and [StatsTest](backend/tests/Feature/StatsTest.php) |
| Vue composition and shared state | [DashboardView](frontend/src/views/DashboardView.vue), [Pinia store](frontend/src/stores/wins.js) and [Axios client](frontend/src/api/http.js) |
| Form and interaction design | [WinForm](frontend/src/components/WinForm.vue), [AppModal](frontend/src/components/AppModal.vue), retries, confirmation and success feedback |
| Verification and reproducible setup | [API tests](backend/tests/Feature/WinsApiTest.php), lockfiles, environment examples and the checks above |

This repository is a focused Laravel and Vue portfolio project. It demonstrates the implementation shown here, with small, reviewable commits following [PLAN.md](PLAN.md).

## Scope and tradeoffs

The intended public demo shares one set of wins across visitors. Authentication, user accounts, permissions, uploads, queues and real-time features are outside v1. The list is unpaginated for the small demo dataset; statistics are computed on request. SQLite keeps local setup simple, while production requires PostgreSQL.
