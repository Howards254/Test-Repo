<!-- plansync -->
# Gemini Instructions — T010: README & setup docs

## Project
Simple PHP User Management System: A lightweight user management system built with plain PHP 8.2+, MySQL (PDO), and Bootstrap 5. Provides authentication, role-based access control (admin/user), full CRUD for users with search and pagination, and a self-service user profile with password change. Uses PSR-4 autoloading via Composer, server-rendered views, and standard security practices (CSRF tokens, prepared statements, output escaping, bcrypt password hashing).

## Your Task
Write the README documenting how to install, configure the database, seed the admin user, run the local dev server, and the default credentials plus security notes.

## File Scope
You may write to files matching:
  - README.md

Files outside this scope are read-only. This project uses filesystem-level permissions to enforce scope boundaries.

## Acceptance Criteria
  - README.md documents prerequisites: PHP 8.2+, Composer, MySQL
  - Includes step-by-step setup: clone, composer install, copy .env.example to .env and fill DB credentials, import database/schema.sql, run php database/seed.php
  - Documents running the app via `php -S localhost:8000 -t public`
  - States default admin credentials (admin@example.com / password123) with an explicit instruction to change them immediately after first login
  - Includes a security notes section summarizing CSRF, prepared statements, bcrypt hashing, and output escaping
  - Lists available routes (login, logout, users CRUD, profile) and the role each requires

## Dependencies
  - T008: Base layout & Bootstrap UI
  - T009: Security hardening pass
<!-- end-plansync -->
