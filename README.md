# TaskFlow

A task management dashboard built with **Laravel 13**, Blade and MySQL. Each account can only see and modify its own tasks.

## Features

- Account registration, login and logout with Laravel's built-in session guard
- Owner-scoped task CRUD with IDOR protection
- Priority, status, due date and notes
- Search by title or description and filter by status
- Request validation with friendly error feedback
- CSRF protection, mass-assignment allow-listing and escaped Blade output
- MySQL-ready configuration; SQLite is used by the automated test suite

## Quick start

```bash
cp .env.example .env
composer install
php artisan key:generate
# Create a MySQL database named taskflow and update .env credentials
php artisan migrate
php artisan serve
```

Open `http://localhost:8000`, create an account, then manage private tasks.

## Tests

```bash
php artisan test
```

## Security notes

Authentication uses Laravel's built-in session guard. Every task query is scoped to the authenticated user, direct access to another user's task returns 404, and normal web CSRF middleware remains enabled. Password reset and email verification are not included yet.
