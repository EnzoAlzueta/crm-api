# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run all tests
composer test

# Run a single test file
php artisan test tests/Feature/ClientApiTest.php

# Run a single test method
php artisan test --filter test_store_assigns_authenticated_user

# Lint / format with Pint
./vendor/bin/pint

# Start dev server
php artisan serve

# Run migrations
php artisan migrate

# Start full dev stack (server + queue + logs + vite)
composer dev
```

---

## Architecture

This is a **Laravel 12 REST API** for a CRM, versioned under `Api\V1`. All routes are prefixed with `/api` (via `bootstrap/app.php`) and protected by **Laravel Sanctum** token authentication, except `POST /api/auth/register` and `POST /api/auth/login`.

### Ownership model

Every resource belongs to the authenticated user via a `user_id` foreign key. Controllers enforce this with an `ensureOwnership()` helper that calls `abort_unless($resource->user_id === $request->user()->id, 403)`. There is no role/permission system — ownership is the only access control.

### Resource hierarchy

```
User
 └── Client
      ├── Contact
      ├── Note       (optionally also linked to a Contact)
      └── Activity   (optionally also linked to a Contact)
```

- **Contacts**, **Notes**, and **Activities** are always scoped to a `Client`.
- Notes and Activities carry an optional `contact_id` to associate them with a specific contact of that client.
- Creation routes for nested resources use `/clients/{client}/{contacts|notes|activities}`. Read/update/delete use flat `apiResource` routes (`/contacts/{contact}`, `/notes/{note}`, `/activities/{activity}`).

---

## API Endpoints

### Auth (`/api/auth`)
| Method | URL | Auth | Description |
|--------|-----|------|-------------|
| POST | `/auth/register` | — | Register, returns Sanctum token + user |
| POST | `/auth/login` | — | Login, returns Sanctum token + user |
| GET | `/auth/user` | ✓ | Get authenticated user |
| POST | `/auth/logout` | ✓ | Revoke current token |

### Dashboard
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/dashboard` | Stats: clients (total + by_status), contacts, notes, activities (total + completed + pending), recent_activities |

### Clients (`/api/clients`)
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/clients` | Paginated list (10/page), scoped to auth user |
| POST | `/clients` | Create — fields: `name`*, `email`, `phone`, `company`, `status` |
| GET | `/clients/{id}` | Show |
| PUT | `/clients/{id}` | Update |
| DELETE | `/clients/{id}` | Soft delete |
| POST | `/clients/{id}/restore` | Restore soft-deleted client |

`status` values: `lead` · `active` · `inactive` · `churned`

### Contacts (`/api/contacts`)
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/contacts` | All contacts across all clients of auth user (paginated) |
| GET | `/clients/{client}/contacts` | Contacts for a specific client |
| POST | `/clients/{client}/contacts` | Create — fields: `name`*, `email`, `phone`, `position` |
| GET | `/contacts/{id}` | Show |
| PUT | `/contacts/{id}` | Update |
| DELETE | `/contacts/{id}` | Soft delete |

### Notes (`/api/notes`)
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/notes` | All notes across all clients (paginated) |
| GET | `/clients/{client}/notes` | Notes for a specific client |
| POST | `/clients/{client}/notes` | Create — fields: `body`*, `contact_id` |
| GET | `/notes/{id}` | Show |
| PUT | `/notes/{id}` | Update — fields: `body`, `contact_id` |
| DELETE | `/notes/{id}` | Soft delete |

### Activities (`/api/activities`)
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/activities` | All activities across all clients (paginated) |
| GET | `/clients/{client}/activities` | Activities for a specific client |
| POST | `/clients/{client}/activities` | Create — fields: `title`*, `type`, `body`, `due_at`, `completed_at`, `contact_id` |
| GET | `/activities/{id}` | Show |
| PUT | `/activities/{id}` | Update |
| DELETE | `/activities/{id}` | Soft delete |

`type` values: `call` · `email` · `meeting` · `task` (default: `task`)

---

## Backend Conventions

- Business logic goes in Services, not Controllers
- Always use Form Requests for validation (`app/Http/Requests/`)
- Format responses with API Resources (`app/Http/Resources/`)
- Never skip `ensureOwnership()` on any endpoint
- Do not add roles/permissions — ownership is the only access control
- Do not put business logic in Controllers

---

## Frontend (`resources/views/app.blade.php`)

Single-file SPA served at `/` (route `web.php`). Uses **Alpine.js** (CDN) for reactivity and **axios** (CDN) for API calls. No build step. Auth token stored in `localStorage` (`crm_token`, `crm_user`).

### Design system
- **Fonts**: `Outfit` (UI) + `Space Mono` (numbers) via Google Fonts
- **Palette**: `--bg: #09090F`, `--surface: #101018`, `--accent: #C8A951` (gold), semantic colors (`--ok`, `--warn`, `--err`, `--info`, `--violet`)
- **CSS**: single `<style>` block with CSS custom properties; no framework

### Layout

```
┌──────────────────────────────────────────┐
│  Sidebar (220px / 60px collapsed)        │
│  ┌────────────────────────────────────┐  │
│  │  Logo + toggle                     │  │
│  │  Nav: Dashboard · Clients ·        │  │
│  │       Contacts · Notes · Activities│  │
│  │  Footer: user avatar + sign out    │  │
│  └────────────────────────────────────┘  │
│                                          │
│  Main area                               │
│  ┌────────────────────────────────────┐  │
│  │  Header (sticky, page title)       │  │
│  │  Content area (section per nav)    │  │
│  └────────────────────────────────────┘  │
└──────────────────────────────────────────┘
```

### Sections

| Section | Content |
|---------|---------|
| `dashboard` | 4 stat cards + clients-by-status bars + recent activities list |
| `clients` | Table with search + status filter, full CRUD, click row → drawer |
| `contacts` | Read-only global table (manage contacts from client drawer) |
| `notes` | Table with full CRUD — create requires client selector |
| `activities` | Table with type + status filters, full CRUD — create requires client selector |

### Client drawer

Slides in from the right when clicking a client row. Four tabs:

| Tab | Content |
|-----|---------|
| Info | All client fields read-only |
| Contacts | List + add/edit/delete contacts |
| Notes | List + add/edit/delete notes |
| Activities | List + add/edit/delete activities |

### Modal

Single generic modal controlled by `modal.{ open, mode, entity, title, data, saving }`.

- `entity`: `client` · `contact` · `note` · `activity`
- `mode`: `create` · `edit`
- When creating from a global section (Notes/Activities), `modal.data._needClient = true` shows a client selector populated from `clientsAll`

### Alpine.js state (key properties)

```js
token, user                        // auth session
section                            // active nav section
sidebarCollapsed                   // sidebar state
stats, dashLoading                 // dashboard
clients, clientsMeta, clientsPage, clientsFilter, clientsLoading
contacts, contactsMeta, contactsPage, contactsLoading
notes, notesMeta, notesPage, notesLoading
activities, activitiesMeta, activitiesPage, activitiesFilter, activitiesLoading
drawerClient, drawerTab, drawerContacts, drawerNotes, drawerActivities, drawerLoading
modal, clientsAll
toasts
```

### Key methods

| Method | Description |
|--------|-------------|
| `navigate(sec)` | Switch section + trigger loader |
| `openDrawer(client)` | Open drawer, fetch contacts/notes/activities in parallel |
| `openModal(mode, entity, data)` | Open generic modal |
| `openModalGlobal(entity)` | Open modal for global create (loads `clientsAll` first) |
| `saveEntity()` | Dispatch to `saveClient/Contact/Note/Activity` |
| `toast(msg, type)` | Push toast, auto-remove after 3.5s |

---

## Testing

Tests use **SQLite in-memory** (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` in `phpunit.xml`) with `RefreshDatabase`. The base `TestCase` provides `authenticateSanctum(?User $user)` which calls `Sanctum::actingAs()` — use this in every feature test that needs an authenticated user.

Test files:
- `tests/Feature/AuthSanctumTest.php`
- `tests/Feature/ClientApiTest.php`
- `tests/Feature/ContactApiTest.php`
- `tests/Feature/NotesAndActivitiesTest.php`
