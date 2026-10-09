# TaskFlow

A polished task management dashboard built with **Laravel 13**, Blade and MySQL. It demonstrates server-side validation, safe CSRF-protected CRUD actions, search/filtering, pagination and responsive UI without claiming prior production experience.

## Features

- Create, complete/reopen and delete tasks
- Priority, status, due date and notes
- Search by title or description and filter by status
- Request validation with friendly error feedback
- CSRF protection, mass-assignment allow-listing and escaped Blade output
- MySQL-ready configuration; SQLite is convenient for local tests

## Quick start

```bash
cp .env.example .env
composer install
php artisan key:generate
# Create a MySQL database named taskflow and update .env credentials
php artisan migrate
php artisan serve
```

Open `http://localhost:8000`. For a quick local smoke test without MySQL, set `DB_CONNECTION=sqlite` and `DB_DATABASE=database/database.sqlite`, create the file, then run migrations.

## Tests

```bash
php artisan test
```

## Security notes

This demo intentionally avoids hard-coded secrets, uses Laravel's CSRF middleware, validates every write, limits input lengths, escapes output in Blade, and uses Eloquent's fillable allow-list. Add authentication/authorization before exposing multi-user data.
