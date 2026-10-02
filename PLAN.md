# Daily Wins Tracker — Codex Execution Plan

> Goal: build a small but complete portfolio project in roughly 1–2 focused hours, using **Laravel + Vue 3**, with one iteration per Git commit and a final public deployment.
>
> The app is intentionally small. The point is to demonstrate clean backend fundamentals, REST APIs, Laravel conventions, Vue 3, state management, responsive UI, tests, deployment, and the ability to explain the architecture.

---

## 0. Product summary

**Daily Wins Tracker** is a simple web app for recording small daily victories.

Examples:

- Went to the gym.
- Started a conversation with someone new.
- Finished a technical exercise.
- Meditated for 25 minutes.
- Applied to a job.
- Cooked instead of ordering food.

The user can create, edit, delete, search and filter wins. The dashboard also shows a few simple statistics.

This is a **single-user public demo**. Authentication is deliberately excluded from v1 to keep the scope interview-friendly.

---

# 1. Portfolio objective

This project should make it easy to demonstrate:

### Laravel / PHP

- Laravel routing.
- REST API design.
- Controllers.
- Eloquent models.
- Migrations.
- Relationships.
- Form Request validation.
- Query Builder / Eloquent filtering.
- JSON Resources if useful.
- Service/domain logic where it actually adds value.
- Database seeders.
- Feature tests.
- Environment-based configuration.
- Production deployment.

### Vue

- Vue 3.
- Composition API.
- Vite.
- Pinia.
- Axios.
- Reusable components.
- Forms and validation feedback.
- Loading, empty and error states.
- Filtering/search.
- Responsive mobile-first design.

### General engineering

- Git history with meaningful commits.
- Separation between frontend and backend.
- `.env.example`.
- README.
- API documentation.
- Deployment.
- Clean naming.
- No proprietary code.
- No fake professional experience.

---

# 2. Final architecture

```text
daily-wins/
├── backend/                  # Laravel REST API
│   ├── app/
│   ├── database/
│   ├── routes/
│   ├── tests/
│   ├── Dockerfile
│   └── ...
│
├── frontend/                 # Vue 3 SPA
│   ├── src/
│   ├── public/
│   └── ...
│
├── README.md
└── PLAN.md
```

### Local

```text
Vue 3 / Vite
http://localhost:5173
        |
        | Axios
        v
Laravel API
http://localhost:8000/api
        |
        v
SQLite
```

### Public demo

```text
Firebase Hosting
Vue SPA
        |
        | HTTPS / Axios
        v
Render Free
Laravel API
        |
        v
Neon PostgreSQL Free
```

Why this deployment:

- Firebase Hosting is excellent for a Vue static SPA.
- Render Free can host the Laravel API as a Docker web service.
- Neon gives us persistent PostgreSQL without relying on Render's temporary free Postgres database.
- This is intended as a portfolio/demo deployment, not production infrastructure.

> Note: Render free web services may sleep after inactivity. The first API request can therefore take noticeably longer. The UI must handle this gracefully with a loading state.

---

# 3. Scope

## Included

### Dashboard

Show:

- Total wins.
- Wins this week.
- Current streak.
- Recent wins.

### Win CRUD

A win has:

```text
id
title
description (nullable)
category_id
win_date
created_at
updated_at
```

### Categories

Seed these categories:

```text
Career
Health
Social
Learning
Mindfulness
Personal
Other
```

### Filters

- Search by title/description.
- Filter by category.
- Sort newest first.

### Responsive design

The UI must be **mobile-first**.

Target widths:

- 320px+
- 375px+
- 768px+
- desktop

No horizontal scrolling.

### UX states

Every async screen must handle:

- loading
- error
- empty result
- success

---

# 4. Out of scope for v1

Do NOT add these unless the core application is already finished and stable:

- Authentication.
- OAuth.
- Laravel Sanctum.
- Email.
- Password reset.
- Notifications.
- WebSockets.
- Redis.
- Queues.
- Docker Compose locally.
- File uploads.
- AI features.
- Payments.
- Multi-user permissions.
- Complex charts.
- Dark mode.
- Internationalization.
- Nuxt.
- TypeScript migration.
- Component libraries.

The project should remain intentionally small.

---

# 5. Domain model

## categories

```text
id
name
slug
created_at
updated_at
```

## wins

```text
id
title varchar(120)
description text nullable
category_id foreign key
win_date date
created_at
updated_at
```

Relationships:

```text
Category hasMany Wins
Win belongsTo Category
```

---

# 6. API contract

Base URL:

```text
/api
```

## Health

```http
GET /api/health
```

Response:

```json
{
  "status": "ok"
}
```

---

## Categories

```http
GET /api/categories
```

Response:

```json
[
  {
    "id": 1,
    "name": "Career",
    "slug": "career"
  }
]
```

---

## Wins

### List

```http
GET /api/wins
```

Optional query params:

```text
search=
category=
```

Example:

```http
GET /api/wins?search=gym&category=health
```

Response:

```json
{
  "data": [
    {
      "id": 1,
      "title": "Went to the gym",
      "description": "Trained even though I felt tired.",
      "win_date": "2026-10-02",
      "category": {
        "id": 2,
        "name": "Health",
        "slug": "health"
      }
    }
  ]
}
```

### Create

```http
POST /api/wins
```

Payload:

```json
{
  "title": "Went to the gym",
  "description": "Trained even though I felt tired.",
  "category_id": 2,
  "win_date": "2026-10-02"
}
```

### Read

```http
GET /api/wins/{id}
```

### Update

```http
PUT /api/wins/{id}
```

### Delete

```http
DELETE /api/wins/{id}
```

Return:

```http
204 No Content
```

---

## Stats

```http
GET /api/stats
```

Response:

```json
{
  "total_wins": 48,
  "wins_this_week": 12,
  "current_streak": 7
}
```

### Streak definition

A streak is the number of consecutive calendar days ending:

- today, if there is at least one win today; or
- yesterday, if today has no wins but yesterday does.

Multiple wins in the same day count as one streak day.

Keep the implementation simple and readable.

---

# 7. UI

## Mobile layout

```text
┌───────────────────────────┐
│ Daily Wins                │
│ Small progress matters.   │
│                           │
│ [ + Add Win ]             │
├───────────────────────────┤
│ 48 total                  │
│ 12 this week              │
│ 7 day streak              │
├───────────────────────────┤
│ Search wins...            │
│ [All categories ▼]        │
├───────────────────────────┤
│ TODAY                     │
│                           │
│ Career                    │
│ Finished Laravel API      │
│ Oct 2                     │
│                    Edit ⋮ │
│                           │
│ Health                    │
│ Went to the gym           │
│ Oct 2                     │
└───────────────────────────┘
```

Desktop can use a centered container, a three-card stats row and a wider list.

---

# 8. Suggested Vue component structure

```text
src/
├── api/
│   └── http.js
├── components/
│   ├── AppHeader.vue
│   ├── StatsGrid.vue
│   ├── WinCard.vue
│   ├── WinForm.vue
│   ├── WinList.vue
│   ├── WinFilters.vue
│   ├── AppModal.vue
│   ├── LoadingState.vue
│   ├── EmptyState.vue
│   └── ErrorState.vue
├── stores/
│   └── wins.js
├── views/
│   └── DashboardView.vue
├── App.vue
└── main.js
```

Avoid component fragmentation for its own sake.

---

# 9. Visual direction

Keep it polished but restrained.

Principles:

- Mobile first.
- Neutral background.
- White cards.
- Strong readable typography.
- Rounded cards.
- Subtle borders/shadows.
- One restrained accent color.
- Lots of whitespace.
- Minimum 44px touch targets.
- Visible focus states.
- Semantic buttons.
- Good contrast.
- No giant gradients.
- No excessive animation.

Do not install a full UI framework.

Plain CSS is enough.

---

# 10. Codex operating rules

Paste this once at the beginning of the Codex session.

```text
You are helping me build a portfolio project called Daily Wins Tracker.

STACK
- Laravel backend REST API.
- Vue 3 + Vite frontend.
- Pinia.
- Axios.
- SQLite locally.
- PostgreSQL in production.
- Mobile-first responsive UI.

IMPORTANT WORKFLOW RULES

1. Read PLAN.md completely before making changes.
2. Inspect the repository before editing anything.
3. Work on ONLY the iteration I explicitly request.
4. Never implement future iterations early.
5. Keep the solution simple and idiomatic.
6. Do not add authentication or extra infrastructure.
7. Do not add dependencies unless they clearly reduce complexity.
8. After each iteration:
   - run the relevant tests,
   - run builds/linters if available,
   - fix failures,
   - summarize changed files,
   - show me how to manually verify it.
9. Each iteration must end in exactly ONE Git commit.
10. Use the commit message defined in PLAN.md unless there is a strong reason to improve it.
11. Never commit secrets, .env files, vendor, node_modules, database files or generated build artifacts unless they belong in source control.
12. Maintain .env.example files.
13. Prefer readable code over clever abstractions.
14. Keep the API contract in PLAN.md stable.
15. If something is ambiguous, choose the smallest implementation that satisfies the acceptance criteria.
16. Do not claim an iteration is complete until its acceptance criteria pass.
17. Do not proceed to the next iteration until I explicitly ask.

Before starting an iteration, state in 3-5 bullets what you will change.
After finishing it, give me:
- commit hash,
- tests/build commands executed,
- result,
- manual verification steps,
- any relevant caveat.
```

---

# 11. Iterations / commits

The project is intentionally divided into small commits.

---

## ITERATION 1 — Scaffold

### Prompt for Codex

```text
Execute ITERATION 1 from PLAN.md only.

Create the repository structure and scaffold:
- Laravel backend inside /backend.
- Vue 3 + Vite frontend inside /frontend.
- Pinia.
- Axios.
- root README.md with a short project description.
- sensible root .gitignore if needed.

Configure Laravel for local SQLite development.

Add GET /api/health returning {"status":"ok"}.

Do not implement wins/categories yet.

Run the backend tests and frontend production build.

Commit everything as:
chore: scaffold Laravel and Vue applications
```

### Acceptance criteria

- `backend/` starts successfully.
- `frontend/` starts successfully.
- `GET /api/health` returns 200.
- Pinia installed/configured.
- Axios installed.
- SQLite local config documented.
- `php artisan test` passes.
- `npm run build` passes.

### Commit

```text
chore: scaffold Laravel and Vue applications
```

---

## ITERATION 2 — Domain + database

### Prompt for Codex

```text
Execute ITERATION 2 from PLAN.md only.

Implement the Laravel domain model:
- Category model + migration.
- Win model + migration.
- Category hasMany Wins.
- Win belongsTo Category.
- CategorySeeder with the categories from PLAN.md.
- DemoWinsSeeder with 6-8 realistic sample wins.
- DatabaseSeeder calls both.

Use proper foreign keys and indexes where reasonable.

Do not implement controllers or frontend UI yet.

Run migrate:fresh --seed and tests.

Commit as:
feat: add wins and categories domain model
```

### Acceptance criteria

- `php artisan migrate:fresh --seed` succeeds.
- Categories exist.
- Sample wins exist.
- Relationships work in Tinker.
- No duplicated category slugs.
- Tests pass.

### Commit

```text
feat: add wins and categories domain model
```

---

## ITERATION 3 — REST API

### Prompt for Codex

```text
Execute ITERATION 3 from PLAN.md only.

Implement the Laravel REST API described in PLAN.md.

Create:
- category listing endpoint.
- WinController CRUD.
- Form Request validation for create/update.
- API resources if they improve consistency.
- search filter.
- category slug filter.
- newest win_date first, then newest created_at.
- clear 404 and validation behaviour using Laravel conventions.

Endpoints:
GET /api/categories
GET /api/wins
POST /api/wins
GET /api/wins/{win}
PUT /api/wins/{win}
DELETE /api/wins/{win}

Do not implement stats yet.

Add focused Feature tests for:
- listing wins.
- creating a win.
- validation failure.
- updating a win.
- deleting a win.
- filtering by category.
- searching.

Run all tests.

Commit as:
feat: implement wins REST API
```

### Acceptance criteria

- CRUD works.
- Validation errors are JSON 422.
- Invalid win id returns 404.
- Search works.
- Category filter works.
- Feature tests pass.

### Commit

```text
feat: implement wins REST API
```

---

## ITERATION 4 — Statistics endpoint

### Prompt for Codex

```text
Execute ITERATION 4 from PLAN.md only.

Implement GET /api/stats.

Return:
- total_wins
- wins_this_week
- current_streak

Follow the streak definition in PLAN.md exactly.

Keep streak calculation readable and isolated from the controller if that makes the controller cleaner.

Add Feature tests covering:
- no wins.
- multiple wins on one day only count as one streak day.
- consecutive-day streak.
- streak can end yesterday if today has no win.
- a gap breaks the streak.

Run all tests.

Commit as:
feat: add dashboard statistics
```

### Acceptance criteria

- API shape matches PLAN.
- Streak behavior is tested.
- Tests pass.

### Commit

```text
feat: add dashboard statistics
```

---

## ITERATION 5 — Vue dashboard + read flow

### Prompt for Codex

```text
Execute ITERATION 5 from PLAN.md only.

Build the Vue dashboard read experience.

Implement:
- Axios client using VITE_API_BASE_URL.
- Pinia wins store.
- fetch wins.
- fetch categories.
- fetch stats.
- DashboardView.
- AppHeader.
- StatsGrid.
- WinList.
- WinCard.
- WinFilters.
- loading state.
- empty state.
- API error state.
- search.
- category filter.

Use a mobile-first responsive design using plain CSS.

Do not implement create/edit/delete UI yet.

The app should look polished enough for a portfolio but remain simple.

Run npm run build.

Commit as:
feat: build responsive wins dashboard
```

### Acceptance criteria

- Works at 320px width.
- No horizontal scrolling.
- Stats display.
- Wins display.
- Search works through API query.
- Category filter works.
- Loading state visible.
- Empty state exists.
- Error state exists.
- `npm run build` passes.

### Commit

```text
feat: build responsive wins dashboard
```

---

## ITERATION 6 — Full CRUD UI

### Prompt for Codex

```text
Execute ITERATION 6 from PLAN.md only.

Add create, edit and delete interactions to the Vue application.

Implement:
- Add Win button.
- reusable WinForm.
- modal or mobile-friendly dialog.
- create win.
- edit win.
- delete win with confirmation.
- client-side required-field feedback.
- display Laravel validation errors when returned.
- disable submit button while saving.
- refresh/update Pinia state after mutations.
- refresh stats after mutations.
- simple success feedback.

Keep the interface accessible and mobile friendly.

Do not add authentication.

Run npm run build and backend tests.

Commit as:
feat: add win creation editing and deletion
```

### Acceptance criteria

- Create works.
- Edit works.
- Delete works.
- Validation is visible.
- Double submissions are prevented.
- Stats refresh after CRUD.
- Mobile form is usable.
- Build/tests pass.

### Commit

```text
feat: add win creation editing and deletion
```

---

## ITERATION 7 — Portfolio polish

### Prompt for Codex

```text
Execute ITERATION 7 from PLAN.md only.

Polish this project for GitHub portfolio presentation without expanding product scope.

Tasks:
- review mobile-first layout at 320, 375, 768 and desktop widths.
- improve spacing/typography where needed.
- make keyboard focus states visible.
- ensure buttons have accessible labels.
- ensure dates are presented cleanly.
- add a small footer noting this is a portfolio demo built with Laravel and Vue.
- improve README.md substantially.

README must include:
- concise product explanation.
- screenshots placeholder section.
- tech stack.
- architecture diagram in Markdown.
- features.
- local requirements.
- local installation.
- backend commands.
- frontend commands.
- environment variables.
- API endpoints.
- test instructions.
- deployment architecture.
- note explaining that the public demo uses free-tier infrastructure and may have a cold start.
- section called "What this project demonstrates".
- no false claims about professional Laravel experience.

Also add:
- backend/.env.example fields needed.
- frontend/.env.example with VITE_API_BASE_URL.

Run all backend tests and frontend build.

Commit as:
docs: prepare project for portfolio
```

### Acceptance criteria

- README is sufficient for a recruiter/developer to understand and run the project.
- `.env.example` files contain no secrets.
- Mobile UI polished.
- Tests/build pass.

### Commit

```text
docs: prepare project for portfolio
```

---

# 12. ITERATION 8 — Production deployment

This is the **last functional iteration**.

Do this after the GitHub repository exists and all previous commits are pushed.

## Services

### Database

Neon PostgreSQL Free.

### Backend

Render Free Web Service using Docker.

### Frontend

Firebase Hosting Spark / no-cost tier.

---

## Prompt for Codex

```text
Execute ITERATION 8 from PLAN.md only.

Prepare the existing project for production deployment using:

- Neon PostgreSQL for the production database.
- Render Free Web Service for the Laravel backend.
- Firebase Hosting for the Vue frontend.

Do not deploy anything automatically unless I explicitly provide credentials/access and ask you to do so.
Prepare all repository code/config/docs required for me to perform the deployment.

BACKEND REQUIREMENTS

1. Add a production Dockerfile suitable for running Laravel on Render with a small memory footprint.
2. The container must listen on Render's PORT environment variable.
3. Do not use SQLite in production.
4. Production database must use standard Laravel PostgreSQL environment variables / DATABASE_URL as appropriate.
5. APP_KEY must come from an environment variable.
6. Add /api/health if it is not already present.
7. Configure trusted production CORS using an environment variable for the frontend origin.
8. Document the Render build/start/deploy configuration.
9. Document how to run migrations and seed demo data safely.
10. Do not commit secrets.

FRONTEND REQUIREMENTS

1. Use VITE_API_BASE_URL.
2. Add Firebase Hosting configuration for a Vue SPA.
3. Configure SPA fallback to index.html.
4. Ensure production build works.
5. Document deployment commands.

README

Add exact step-by-step deployment instructions:
- create Neon project.
- obtain PostgreSQL connection information.
- create Render Web Service from GitHub.
- configure Render environment variables.
- deploy backend.
- run migrations + seed.
- create Firebase project.
- set frontend API URL.
- build.
- firebase deploy.
- update backend allowed frontend origin if necessary.
- verify end-to-end.

Add a deployment verification checklist.

Run all tests and frontend production build.

Commit as:
chore: prepare free-tier production deployment
```

### Acceptance criteria

- Backend Docker image builds.
- Backend works against PostgreSQL configuration.
- Frontend build succeeds.
- Firebase config exists.
- No secret exists in Git.
- Production CORS is documented.
- README has deployment steps.
- Tests pass.

### Commit

```text
chore: prepare free-tier production deployment
```

---

# 13. FINAL DEPLOYMENT COMMIT

After manually deploying and verifying the live application:

### Tasks

1. Put the live frontend URL into `README.md`.
2. Put the live API health URL into `README.md`.
3. Add screenshots.
4. Verify GitHub repository description.
5. Add repository topics.
6. Verify clean clone instructions.
7. Test the public site from a phone.
8. Test create/edit/delete against production.
9. Verify no `.env` or secret was committed.

### Suggested GitHub topics

```text
laravel
php
vue
vue3
pinia
vite
rest-api
postgresql
responsive-design
portfolio
```

### Final commit

```text
docs: add live demo and screenshots
```

---

# 14. Deployment verification checklist

```text
[ ] GitHub repository is public.
[ ] README renders correctly.
[ ] Live demo URL works.
[ ] HTTPS works.
[ ] Vue loads on mobile.
[ ] API health endpoint returns 200.
[ ] Categories load.
[ ] Existing wins load.
[ ] Create win works.
[ ] Edit win works.
[ ] Delete win works.
[ ] Search works.
[ ] Category filter works.
[ ] Stats update after CRUD.
[ ] Refreshing a frontend route does not cause 404.
[ ] CORS only allows intended origins.
[ ] No secret appears in repository history.
[ ] php artisan test passes.
[ ] npm run build passes.
```

---

# 15. 1–2 hour execution strategy

Keep momentum high.

Approximate effort, not a promise:

```text
Iteration 1   Scaffold                very short
Iteration 2   Models/database         short
Iteration 3   API CRUD                medium
Iteration 4   Stats                   short
Iteration 5   Dashboard               medium
Iteration 6   CRUD UI                 medium
Iteration 7   Portfolio polish        short
Iteration 8   Deployment config       medium
Manual deploy                          variable
```

If time becomes tight, protect this order:

```text
MUST HAVE
1. Laravel migrations/models
2. CRUD API
3. Vue dashboard
4. Create/edit/delete
5. Responsive mobile layout
6. Tests
7. GitHub README

SHOULD HAVE
8. Stats/streak
9. Search/filter
10. Public deployment

NICE TO HAVE
11. Visual polish
12. Screenshots
```

Do not trade correctness for extra features.

---

# 16. Interview walkthrough

The final app should support a 2–3 minute technical walkthrough.

Suggested flow:

### 1. Show the live application

```text
"This is a small Daily Wins Tracker that I built with Vue 3 and Laravel."
```

Create a win.

### 2. Open browser Network tab

Show:

```text
POST /api/wins
```

Explain that Vue communicates with Laravel through a REST API.

### 3. Show Vue

Open:

```text
frontend/src/stores/wins.js
frontend/src/api/http.js
```

Explain:

- Pinia state.
- Axios API integration.
- Composition API.

### 4. Show Laravel route + controller

Open:

```text
backend/routes/api.php
backend/app/Http/Controllers/...
```

Explain:

```text
Route -> validation -> controller -> Eloquent -> database -> JSON
```

### 5. Show model relationship

```php
Win belongsTo Category
Category hasMany Wins
```

### 6. Show migration

Explain that local development uses SQLite while the deployed app uses PostgreSQL with the same Laravel migrations.

### 7. Show one Feature test

Explain that the API behavior is covered by automated tests.

### 8. Be transparent

Suggested wording:

```text
Laravel has not been the main PHP framework in my previous professional work.
My previous backend experience is mainly PHP, MVC and CodeIgniter.

I built this project to work directly with Laravel and demonstrate how my PHP
and MVC experience transfers to its routing, controllers, Eloquent models,
validation and REST APIs.
```

That is stronger than pretending.

---

# 17. Definition of done

The project is DONE when:

```text
[ ] Repository is public on GitHub.
[ ] Git history tells a coherent story.
[ ] Laravel API has CRUD + stats.
[ ] Vue app consumes the real Laravel API.
[ ] App works on mobile.
[ ] Backend has Feature tests.
[ ] Frontend production build passes.
[ ] README explains architecture and setup.
[ ] Public demo exists.
[ ] No secrets are committed.
[ ] You can explain every important file you show in the interview.
```

Anything beyond this belongs in a future version.

---

# 18. Possible v2 ideas

Do NOT implement these for the interview unless v1 is completely done.

```text
- Authentication with Laravel Sanctum.
- Per-user wins.
- Weekly charts.
- Habit/practice linking.
- Markdown notes.
- Tags.
- Export to CSV.
- PWA support.
- Dark mode.
- Automated GitHub Actions.
```

They can remain in the README as future ideas if desired.

---

# 19. One-command mental model

When explaining the project, remember this path:

```text
User
  ↓
Vue component
  ↓
Pinia
  ↓
Axios
  ↓
Laravel route
  ↓
Form Request validation
  ↓
Controller
  ↓
Eloquent model
  ↓
PostgreSQL / SQLite
  ↓
JSON response
  ↓
Pinia
  ↓
Vue updates the UI
```

That is the core of the project and the core of the interview story.
