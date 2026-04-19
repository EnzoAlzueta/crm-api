# CRM API

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Sanctum](https://img.shields.io/badge/Auth-Sanctum-FF2D20)
![License](https://img.shields.io/badge/license-MIT-blue)

A portfolio-grade REST API for managing CRM data — clients, contacts, notes, and activities — built with **Laravel 12** and **Sanctum** token authentication.

---

## Features

- Token-based authentication (register, login, logout) via Laravel Sanctum
- Full CRUD for Clients, Contacts, Notes, and Activities
- Ownership-scoped data — each user sees only their own records
- Soft deletes with restore support for clients
- Dashboard endpoint with aggregated stats
- Paginated list endpoints (10 per page)
- Eloquent API Resources for consistent JSON responses
- Form Request validation on every write endpoint
- Auto-generated interactive API docs (Scribe) at `/docs`
- Demo seeder with realistic data

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Authentication | Laravel Sanctum |
| Database | MySQL (SQLite for tests) |
| Docs | Knuckleswtf/Scribe |
| Testing | PHPUnit 11 |
| Code style | Laravel Pint |

---

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- MySQL

### Installation

```bash
git clone https://github.com/enzoalzueta/crm-api.git
cd crm-api

composer install
cp .env.example .env
php artisan key:generate

# Configure DB credentials in .env, then:
php artisan migrate
php artisan db:seed

php artisan serve
```

---

## Demo Credentials

After running `php artisan db:seed`:

| Field | Value |
|---|---|
| Email | `demo@crm.test` |
| Password | `password` |

Open `http://localhost:8000` to log in via the dashboard UI.

---

## API Overview

All endpoints are prefixed with `/api`. Authenticated endpoints require `Authorization: Bearer {token}`.

| Method | Endpoint | Description |
|---|---|---|
| POST | `/auth/register` | Register a new user |
| POST | `/auth/login` | Login (returns token) |
| GET | `/auth/user` | Get current user |
| POST | `/auth/logout` | Revoke current token |
| GET | `/dashboard` | Aggregated stats |
| GET/POST | `/clients` | List / create clients |
| GET/PATCH/DELETE | `/clients/{id}` | Show / update / delete |
| POST | `/clients/{id}/restore` | Restore soft-deleted client |
| GET/POST | `/clients/{id}/contacts` | List / create contacts |
| GET/PATCH/DELETE | `/contacts/{id}` | Show / update / delete |
| GET/POST | `/clients/{id}/notes` | List / create notes |
| GET/PATCH/DELETE | `/notes/{id}` | Show / update / delete |
| GET/POST | `/clients/{id}/activities` | List / create activities |
| GET/PATCH/DELETE | `/activities/{id}` | Show / update / delete |

---

## API Docs

Interactive documentation is available at:

```
http://localhost:8000/docs
```

Generated with [Scribe](https://scribe.knuckles.wtf/laravel). Includes a Postman collection and an OpenAPI 3.0 spec.

---

## Running Tests

```bash
# All tests
composer test

# Single file
php artisan test tests/Feature/ClientApiTest.php

# Single method
php artisan test --filter test_store_assigns_authenticated_user
```

Tests run against an **SQLite in-memory** database — no MySQL required for the test suite.

---

## License

[MIT](LICENSE)
