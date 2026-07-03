<!-- plansync -->
# Copilot Instructions — T010: README & setup docs

## Scope
Only suggest edits for files matching these patterns:
  - README.md

Do not suggest changes to files outside this scope. Treat out-of-scope files as read-only context.

## Task
Write the README documenting how to install, configure the database, seed the admin user, run the local dev server, and the default credentials plus security notes.

## Acceptance Criteria
  - README.md documents prerequisites: PHP 8.2+, Composer, MySQL
  - Includes step-by-step setup: clone, composer install, copy .env.example to .env and fill DB credentials, import database/schema.sql, run php database/seed.php
  - Documents running the app via `php -S localhost:8000 -t public`
  - States default admin credentials (admin@example.com / password123) with an explicit instruction to change them immediately after first login
  - Includes a security notes section summarizing CSRF, prepared statements, bcrypt hashing, and output escaping
  - Lists available routes (login, logout, users CRUD, profile) and the role each requires

## Context
- Project: Simple PHP User Management System
- Depends on:   - T008: Base layout & Bootstrap UI
  - T009: Security hardening pass
<!-- end-plansync -->
