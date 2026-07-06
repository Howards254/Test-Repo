<!-- plansync -->
# Project: Simple PHP User Management System

## Your Assignment{{#if multiTask}}s{{/if}} — Howards254

You have 5 task(s) assigned to you.

### T001: Project scaffolding & config
Set up the directory structure, PSR-4 autoloading via Composer, environment/config loading, the public document root with .htaccess rewrite rules, and a front controller (public/index.php) that bootstraps the app and dispatches to a router.

**Scope:** composer.json, public/index.php, public/.htaccess, config/config.php, .env.example, src/Core/Config.php

**Acceptance Criteria:**
  - Directory structure matches the plan: public/, src/{Core,Controllers,Middleware,Models}, views/{layouts,partials,auth,users,profile}, database/, config/
  - composer.json defines PSR-4 autoloading mapping src/ -> App\ namespace
  - public/.htaccess rewrites all non-file requests to index.php
  - public/index.php requires composer autoload, loads config, starts a session, and dispatches the router
  - config/config.php loads .env values with sensible defaults (db host/name/user/pass, app name, base URL)
  - .env.example documents all required environment variables without secrets
  - Running `composer install` then `php -S localhost:8000 -t public` serves the app without fatal errors

**Dependencies:**
  (none — this task has no dependencies)

**Status:** ready

### T002: Database schema & PDO layer
Create the MySQL users table schema and a PDO-based Database singleton connection class. Include a seed script that inserts a default admin user with a bcrypt-hashed password.

**Scope:** database/schema.sql, database/seed.php, src/Core/Database.php

**Acceptance Criteria:**
  - database/schema.sql creates a `users` table with columns: id (PK, auto-increment), name (varchar), email (varchar, unique), password (varchar), role (enum('admin','user'), default 'user'), created_at (timestamp default now)
  - schema.sql uses InnoDB and utf8mb4 charset
  - src/Core/Database.php provides a PDO singleton via Database::connection(), throws a clear RuntimeException on connection failure
  - PDO is configured with ERRMODE_EXCEPTION, default fetch mode FETCH_ASSOC, and emulated prepares disabled
  - database/seed.php inserts a default admin user: admin@example.com / password123 (bcrypt hashed) with role 'admin', and is idempotent (re-running does not duplicate)
  - seed.php can be run via `php database/seed.php` and reports success/failure to stdout

**Dependencies:**
  - T001: Project scaffolding & config

**Status:** ready

### T003: Core infrastructure
Build the shared core utilities the rest of the app depends on: a session wrapper, CSRF token generation and verification, flash messages, request/input helpers, a simple router with route parameters, and a view/template renderer.

**Scope:** src/Core/Session.php, src/Core/Csrf.php, src/Core/Flash.php, src/Core/Request.php, src/Core/Router.php, src/Core/View.php

**Acceptance Criteria:**
  - Session.php provides static helpers: Session::start(), Session::get(), Session::set(), Session::forget(), and starts sessions safely (no double-start warnings)
  - Csrf.php generates a per-session token, exposes Csrf::token() and Csrf::verify($token), and regenerates the token after login/logout
  - Flash.php supports Flash::set('key','message') and Flash::get('key') that reads once and clears; data survives a redirect
  - Request.php exposes Request::method(), Request::input($key,$default), Request::all(), Request::has($key), and Request::path()
  - Router.php supports registering GET/POST routes with controller@method or closure handlers, route parameters (e.g. /users/{id}), and a dispatch() method returning a 404 when no route matches
  - View.php renders a template from views/ with extracted data, supports a layout, and escapes output by default via a helper e($value) returning htmlspecialchars
  - All core classes are namespaced under App\Core and autoload correctly

**Dependencies:**
  - T001: Project scaffolding & config
  - T002: Database schema & PDO layer

**Status:** ready

### T009: Security hardening pass
Audit and enforce security practices across the app: CSRF protection on every state-changing request, output escaping everywhere, prepared statements for all DB queries, and a password strength policy.

**Scope:** src/Core/Csrf.php, src/Core/View.php, src/Controllers/AuthController.php, src/Controllers/UserController.php, src/Controllers/ProfileController.php, src/Models/User.php

**Acceptance Criteria:**
  - Every POST/PUT/DELETE form includes a hidden csrf_token field and the corresponding controller action verifies it before mutating state; a bad token aborts with 419
  - All user-controlled output is passed through e()/htmlspecialchars (no raw echo of variables in any view) — verified by grep for `<?= # Project: Simple PHP User Management System

## Your Assignment{{#if multiTask}}s{{/if}} — Howards254

You have 5 task(s) assigned to you.

 patterns and confirming escaping
  - Every database query uses a prepared statement with bound parameters (no string concatenation of user input into SQL) — verified by grep for raw query construction
  - Password policy: minimum 8 characters, enforced on register/create/edit/change-password; rejected attempts show a clear validation error
  - Sessions use a reasonable cookie configuration (HttpOnly, SameSite=Lax) set before session start
  - A short manual or scripted security checklist in the README documents these controls

**Dependencies:**
  - T008: Base layout & Bootstrap UI

**Status:** ready

### T010: README & setup docs
Write the README documenting how to install, configure the database, seed the admin user, run the local dev server, and the default credentials plus security notes.

**Scope:** README.md

**Acceptance Criteria:**
  - README.md documents prerequisites: PHP 8.2+, Composer, MySQL
  - Includes step-by-step setup: clone, composer install, copy .env.example to .env and fill DB credentials, import database/schema.sql, run php database/seed.php
  - Documents running the app via `php -S localhost:8000 -t public`
  - States default admin credentials (admin@example.com / password123) with an explicit instruction to change them immediately after first login
  - Includes a security notes section summarizing CSRF, prepared statements, bcrypt hashing, and output escaping
  - Lists available routes (login, logout, users CRUD, profile) and the role each requires

**Dependencies:**
  - T008: Base layout & Bootstrap UI
  - T009: Security hardening pass

**Status:** ready

## File Permissions
You may ONLY edit files matching the glob patterns in your assigned tasks above.
You may READ any file in the project for context, but writes outside your scope will fail with an OS permission error (EACCES).

## Project Summary
A lightweight user management system built with plain PHP 8.2+, MySQL (PDO), and Bootstrap 5. Provides authentication, role-based access control (admin/user), full CRUD for users with search and pagination, and a self-service user profile with password change. Uses PSR-4 autoloading via Composer, server-rendered views, and standard security practices (CSRF tokens, prepared statements, output escaping, bcrypt password hashing).
<!-- end-plansync -->
