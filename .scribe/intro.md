# Introduction

REST API for a CRM application built with **Laravel 12** and **Laravel Sanctum**. Manage clients, contacts, notes, and activities — all data is ownership-scoped to the authenticated user.

<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>

All endpoints except `POST /auth/register` and `POST /auth/login` require a Bearer token. Include it in every request as:

```
Authorization: Bearer {your_token}
```

You obtain a token by calling `POST /auth/login` with your credentials.

