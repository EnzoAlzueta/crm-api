# CRM API — Portfolio Readiness Plan

A structured plan to take the project from functional to portfolio-grade. The goal is a professional, well-documented, visually demonstrable REST API in Laravel 12.

---

## Current State (Quick Audit)

| Area | Status |
|---|---|
| Authentication (Sanctum) | ✅ Working |
| CRUD (clients, contacts, notes, activities) | ✅ Working |
| Ownership checks | ✅ Working |
| Feature tests | ✅ Good coverage |
| Soft deletes | ❌ Missing |
| Form Request classes | ❌ Missing (validation inline in controllers) |
| Eloquent API Resources | ❌ Missing (plain model JSON, no response shaping) |
| Model Factories (non-user) | ❌ Missing |
| Seeders with demo data | ❌ Bare minimum |
| Dashboard / stats endpoint | ❌ Missing |
| API Documentation | ❌ Missing |
| Frontend | ❌ Missing |
| README | ❌ Default Laravel README |
| `composer.json` name | ❌ Still `"laravel/laravel"` |

---

## Proposed Changes

### Phase 1 — Code Quality

---

#### [MODIFY] `composer.json`
- Change `"name"` from `"laravel/laravel"` to `"username/crm-api"` and update `"description"`.

---

#### [NEW] Form Request classes — `app/Http/Requests/`

Extracting inline `$request->validate()` calls from all controllers into dedicated Form Request classes. This is the **standard Laravel practice** and makes controllers thin and readable.

| File | Purpose |
|---|---|
| `StoreClientRequest.php` | Validation for POST /clients |
| `UpdateClientRequest.php` | Validation for PATCH /clients/{id} |
| `StoreContactRequest.php` | Validation for POST /clients/{id}/contacts |
| `UpdateContactRequest.php` | Validation for PATCH /contacts/{id} |
| `StoreNoteRequest.php` | Validation for POST /clients/{id}/notes |
| `UpdateNoteRequest.php` | Validation for PATCH /notes/{id} |
| `StoreActivityRequest.php` | Validation for POST /clients/{id}/activities |
| `UpdateActivityRequest.php` | Validation for PATCH /activities/{id} |

#### [MODIFY] All V1 Controllers
- Replace inline `$request->validate()` calls with the corresponding Form Request type-hints.

---

#### [NEW] Eloquent API Resources — `app/Http/Resources/`

Shapes the JSON responses (consistent structure, hides internal fields, adds metadata). Required for a professional API.

| File | Purpose |
|---|---|
| `UserResource.php` | Hides `password`, exposes `id`, `name`, `email`, `created_at` |
| `ClientResource.php` | Exposes client fields cleanly |
| `ContactResource.php` | Includes link back to client |
| `NoteResource.php` | Exposes body, dates, linked contact |
| `ActivityResource.php` | Exposes type, title, due_at, completed_at |

#### [MODIFY] All V1 Controllers
- Wrap return values in their corresponding Resource or `ResourceCollection`.

---

### Phase 2 — Soft Deletes

---

#### [NEW] Migration — `add_soft_deletes_to_all_tables.php`
- Adds `deleted_at` column to `clients`, `contacts`, `notes`, `activities`.

#### [MODIFY] `Client.php`, `Contact.php`, `Note.php`, `Activity.php`
- Add `use SoftDeletes;` trait.

#### [MODIFY] `routes/api.php`
- Add `POST /clients/{client}/restore` route (and same pattern for contacts, notes, activities if desired — at minimum for clients).

#### [MODIFY] `ClientController.php`
- Add `restore()` method.

> [!NOTE]
> The restore route for contacts/notes/activities can be left out of scope since they're typically managed through their parent client — restoring the client is the main use case.

---

### Phase 3 — Dashboard Stats Endpoint

---

#### [NEW] `app/Http/Controllers/Api/V1/DashboardController.php`
Single `GET /api/dashboard` endpoint (protected by Sanctum). Returns:
```json
{
  "clients": { "total": 42, "by_status": { "lead": 10, "active": 25, "inactive": 7 } },
  "contacts": { "total": 118 },
  "notes": { "total": 74 },
  "activities": { "total": 53, "pending": 12, "completed": 41 },
  "recent_activities": [ ... ]
}
```

#### [MODIFY] `routes/api.php`
- Add `GET /dashboard` inside the `auth:sanctum` group.

---

### Phase 4 — Factories & Seeders

---

#### [NEW] Factories
| File | Notes |
|---|---|
| `ClientFactory.php` | Realistic company names, statuses (lead/active/inactive/churned) |
| `ContactFactory.php` | Linked to a client |
| `NoteFactory.php` | Linked to a client, optional contact |
| `ActivityFactory.php` | Types: call, email, meeting, task; random due_at |

#### [MODIFY] `DatabaseSeeder.php`
Creates a **demo user with credentials shown in the README**, plus:
- 10 clients (mix of statuses)
- 2–4 contacts per client
- 1–3 notes per client
- 1–3 activities per client

---

### Phase 5 — API Documentation (Scribe)

---

**Tool chosen: [Laravel Scribe](https://scribe.knuckles.wtf/laravel)** — the most recommended documentation tool in the Laravel ecosystem. It:
- Auto-generates docs by analyzing routes, type-hints, and docblocks
- Produces a beautiful hosted HTML page at `/docs`
- Exports an **OpenAPI 3.0 spec** (`openapi.yaml`) and a **Postman collection** (`collection.json`)
- Both files can be committed to the repo as portfolio artifacts

#### Steps:
1. `composer require --dev knuckleswtf/scribe`
2. `php artisan vendor:publish --tag=scribe-config`
3. Add docblocks + `@response` annotations to all controllers
4. `php artisan scribe:generate`
5. Commit `/public/docs/` and the generated `openapi.yaml`

#### [MODIFY] All V1 Controllers
- Add PHPDoc blocks with `@group`, `@bodyParam`, `@queryParam`, `@response` annotations.

---

### Phase 6 — Minimal Frontend

---

**Approach: Blade + Alpine.js + Axios (CDN, no Node build step needed for the UI)**

This is the lightest possible frontend, fully served by Laravel itself. It shows real data from the API and is impressive without being a whole separate project.

#### [NEW] `resources/views/app.blade.php`
Single-page Blade template that loads Alpine.js and Axios from CDN. Contains:
- **Login / Register screen** (unauthenticated state)
- **Dashboard screen** (authenticated state) with:
  - Stats cards: Total Clients, Contacts, Notes, Activities
  - Activities breakdown: Pending vs Completed
  - Recent Activities feed
  - Clients by Status (simple bar or list)
  - Logout button

#### [MODIFY] `routes/web.php`
- Return the `app` view for the root `/` route.

#### Design notes
- Dark theme, modern card-based layout
- Uses the `/api/dashboard` endpoint for stats
- No routing needed — Alpine.js manages the two screens (login ↔ dashboard) with a simple `x-show` toggle

---

### Phase 7 — README Rewrite

---

#### [MODIFY] `README.md`
Complete rewrite in English. Structure:

```
# CRM API
[badges: PHP 8.2, Laravel 12, Sanctum, Tests passing, License]

Short elevator pitch (2–3 lines).

## Features
## Tech Stack
## Getting Started (Prerequisites + Installation)
## Demo Credentials
## API Overview (endpoint table)
## API Docs (/docs link)
## Running Tests
## License
```

---

## Verification Plan

### Automated Tests
```bash
composer test  # All existing tests must still pass after refactors
```

### Manual Verification
1. Run `php artisan migrate:fresh --seed` and confirm demo data loads
2. Open `http://localhost:8000` — login with demo credentials, verify dashboard shows stats
3. Open `http://localhost:8000/docs` — verify Scribe documentation is complete
4. Soft delete a client via `DELETE /api/clients/{id}`, confirm it's gone from the list but not the DB
5. Restore via `POST /api/clients/{id}/restore`, confirm it reappears

---

## Execution Order Summary

| # | Task | Est. Scope |
|---|---|---|
| 1 | `composer.json` name fix | Trivial |
| 2 | Form Request classes | Medium |
| 3 | API Resources | Medium |
| 4 | Soft Deletes (migration + models + restore route) | Small |
| 5 | Dashboard stats endpoint | Small |
| 6 | Factories (4 models) + Seeder | Medium |
| 7 | Scribe install + annotations + generate | Medium |
| 8 | Frontend (Blade + Alpine.js) | Medium–Large |
| 9 | README rewrite | Small |

> [!TIP]
> Steps 1–6 are purely backend and fully testable. Steps 7–9 are presentation layer. If splitting across sessions, cut at step 6 for a complete first session.
