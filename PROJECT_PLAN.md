# Simple PHP User Management System

A lightweight user management system built with plain PHP 8.2+, MySQL (PDO), and Bootstrap 5. Provides authentication, role-based access control (admin/user), full CRUD for users with search and pagination, and a self-service user profile with password change. Uses PSR-4 autoloading via Composer, server-rendered views, and standard security practices (CSRF tokens, prepared statements, output escaping, bcrypt password hashing).

## PlanSync Managed Plan

| Task | Title | Assignee | Status | Issue |
|------|-------|----------|--------|-------|
| T001 | Project scaffolding & config | Howards254 | ready | #1 |
| T002 | Database schema & PDO layer | Howards254 | ready | #2 |
| T003 | Core infrastructure | Howards254 | ready | #3 |
| T004 | Authentication | calebomondi | ready | #4 |
| T005 | Roles & permissions middleware | calebomondi | ready | #5 |
| T006 | User CRUD (admin) with search & pagination | calebomondi | ready | #6 |
| T007 | User profile & password change | calebomondi | ready | #7 |
| T008 | Base layout & Bootstrap UI | calebomondi | ready | #8 |
| T009 | Security hardening pass | Howards254 | ready | #9 |
| T010 | README & setup docs | Howards254 | ready | #10 |

## Task Details

### T001: Project scaffolding & config

Set up the directory structure, PSR-4 autoloading via Composer, environment/config loading, the public document root with .htaccess rewrite rules, and a front controller (public/index.php) that bootstraps the app and dispatches to a router.

**Scope:** composer.json, public/index.php, public/.htaccess, config/config.php, .env.example, src/Core/Config.php

**Acceptance Criteria:**
- [ ] Directory structure matches the plan: public/, src/{Core,Controllers,Middleware,Models}, views/{layouts,partials,auth,users,profile}, database/, config/
- [ ] composer.json defines PSR-4 autoloading mapping src/ -> App\ namespace
- [ ] public/.htaccess rewrites all non-file requests to index.php
- [ ] public/index.php requires composer autoload, loads config, starts a session, and dispatches the router
- [ ] config/config.php loads .env values with sensible defaults (db host/name/user/pass, app name, base URL)
- [ ] .env.example documents all required environment variables without secrets
- [ ] Running `composer install` then `php -S localhost:8000 -t public` serves the app without fatal errors

### T002: Database schema & PDO layer

Create the MySQL users table schema and a PDO-based Database singleton connection class. Include a seed script that inserts a default admin user with a bcrypt-hashed password.

**Scope:** database/schema.sql, database/seed.php, src/Core/Database.php

**Acceptance Criteria:**
- [ ] database/schema.sql creates a `users` table with columns: id (PK, auto-increment), name (varchar), email (varchar, unique), password (varchar), role (enum('admin','user'), default 'user'), created_at (timestamp default now)
- [ ] schema.sql uses InnoDB and utf8mb4 charset
- [ ] src/Core/Database.php provides a PDO singleton via Database::connection(), throws a clear RuntimeException on connection failure
- [ ] PDO is configured with ERRMODE_EXCEPTION, default fetch mode FETCH_ASSOC, and emulated prepares disabled
- [ ] database/seed.php inserts a default admin user: admin@example.com / password123 (bcrypt hashed) with role 'admin', and is idempotent (re-running does not duplicate)
- [ ] seed.php can be run via `php database/seed.php` and reports success/failure to stdout

**Dependencies:** T001

### T003: Core infrastructure

Build the shared core utilities the rest of the app depends on: a session wrapper, CSRF token generation and verification, flash messages, request/input helpers, a simple router with route parameters, and a view/template renderer.

**Scope:** src/Core/Session.php, src/Core/Csrf.php, src/Core/Flash.php, src/Core/Request.php, src/Core/Router.php, src/Core/View.php

**Acceptance Criteria:**
- [ ] Session.php provides static helpers: Session::start(), Session::get(), Session::set(), Session::forget(), and starts sessions safely (no double-start warnings)
- [ ] Csrf.php generates a per-session token, exposes Csrf::token() and Csrf::verify($token), and regenerates the token after login/logout
- [ ] Flash.php supports Flash::set('key','message') and Flash::get('key') that reads once and clears; data survives a redirect
- [ ] Request.php exposes Request::method(), Request::input($key,$default), Request::all(), Request::has($key), and Request::path()
- [ ] Router.php supports registering GET/POST routes with controller@method or closure handlers, route parameters (e.g. /users/{id}), and a dispatch() method returning a 404 when no route matches
- [ ] View.php renders a template from views/ with extracted data, supports a layout, and escapes output by default via a helper e($value) returning htmlspecialchars
- [ ] All core classes are namespaced under App\Core and autoload correctly

**Dependencies:** T001, T002

### T004: Authentication

Implement user login and logout with bcrypt password verification, session regeneration on login to prevent fixation, and an Auth helper to check the authenticated user across the app.

**Scope:** src/Controllers/AuthController.php, src/Core/Auth.php, src/Models/User.php, views/auth/login.php

**Acceptance Criteria:**
- [ ] AuthController::showLogin() renders views/auth/login.php with a form posting to the login route
- [ ] AuthController::login() validates email+password presence, looks up the user by email, verifies the bcrypt hash with password_verify(), and on failure re-renders the form with a flash error and old email
- [ ] On success: regenerates the session ID, stores user id in session, regenerates the CSRF token, and redirects to the intended URL or /users
- [ ] AuthController::logout() clears the session, regenerates the session ID and CSRF token, and redirects to /login
- [ ] src/Models/User.php provides static methods: User::findByEmail($email), User::find($id), and is namespaced under App\Models
- [ ] src/Core/Auth.php provides Auth::check(), Auth::id(), Auth::user() (lazy-loads from DB), and Auth::logout()
- [ ] Login form includes the CSRF token as a hidden field and the token is verified on POST
- [ ] Guests can only access /login; attempting other routes redirects to /login

**Dependencies:** T003

### T005: Roles & permissions middleware

Add middleware to enforce authentication and role-based access control. Provide a generic auth middleware that requires a logged-in user and a role middleware that restricts routes to admins.

**Scope:** src/Middleware/AuthMiddleware.php, src/Middleware/RoleMiddleware.php, src/Core/Router.php

**Acceptance Criteria:**
- [ ] AuthMiddleware::handle() redirects unauthenticated users to /login and otherwise allows the request to proceed
- [ ] RoleMiddleware::handle(['admin']) returns a 403 response (or a styled 403 view) when the authenticated user's role is not in the allowed list
- [ ] Router supports attaching middleware to individual routes (e.g. $router->get('/users', [UserController::class,'index'])->middleware('auth','role:admin'))
- [ ] All /users routes (index, create, store, edit, update, delete) are guarded by both auth and role:admin middleware
- [ ] All /profile routes are guarded by auth middleware only
- [ ] A non-admin user hitting /users receives a 403; a logged-out user hitting /users is redirected to /login

**Dependencies:** T004

### T006: User CRUD (admin) with search & pagination

Implement the admin user management screens: a paginated, searchable list of users, plus create, edit, and delete actions. Include server-side validation and flash confirmations.

**Scope:** src/Controllers/UserController.php, src/Models/User.php, views/users/index.php, views/users/create.php, views/users/edit.php

**Acceptance Criteria:**
- [ ] UserController::index() lists users with a search box (filter by name or email via LIKE) and pagination (e.g. 10 per page) using a ?page= and ?q= query string; paginated via SQL LIMIT/OFFSET with prepared statements
- [ ] index view shows a table (name, email, role, created_at) with edit/delete links, the search input preserving ?q=, and pagination links preserving ?q= and ?page=
- [ ] UserController::create() renders a form (name, email, password, role) with CSRF token and validation error display
- [ ] UserController::store() validates required fields + unique email + 8-char min password, hashes the password with bcrypt, inserts the user, flashes success, and redirects to /users
- [ ] UserController::edit($id) renders the edit form pre-filled with the user's data (password field left blank; empty on submit means 'no change')
- [ ] UserController::update($id) validates input, updates fields, optionally re-hashes a new password if provided, flashes success, redirects to /users
- [ ] UserController::delete($id) deletes the user (prevents deleting the currently logged-in admin), flashes success, redirects to /users
- [ ] All list/search/pagination queries use prepared statements; all output is escaped

**Dependencies:** T005

### T007: User profile & password change

Allow the logged-in user to view their own profile, edit their own name/email, and change their own password after confirming the current password.

**Scope:** src/Controllers/ProfileController.php, src/Models/User.php, views/profile/show.php, views/profile/edit.php

**Acceptance Criteria:**
- [ ] ProfileController::show() renders the authenticated user's profile (name, email, role, member since) from views/profile/show.php
- [ ] ProfileController::edit() renders an edit form for name and email, pre-filled, with CSRF token
- [ ] ProfileController::update() validates name (required) and email (required, unique excluding current user), updates the record, flashes success, redirects to /profile
- [ ] ProfileController::changePassword() verifies the current password with password_verify(), validates the new password (8-char min), hashes with bcrypt, updates, flashes success, redirects to /profile
- [ ] Incorrect current password shows a validation error and does not change the password
- [ ] Routes /profile (GET), /profile/edit (GET), /profile (PUT/PATCH), /profile/password (PUT/PATCH) are registered and guarded by auth middleware
- [ ] A regular user cannot edit another user's profile (only their own)

**Dependencies:** T004

### T008: Base layout & Bootstrap UI

Build the shared base layout with Bootstrap 5 (CDN), a navigation bar that adapts to auth state and role, a flash-messages partial, and ensure all views are responsive and consistent.

**Scope:** views/layouts/app.php, views/partials/navbar.php, views/partials/flash.php, views/errors/403.php, views/errors/404.php

**Acceptance Criteria:**
- [ ] views/layouts/app.php includes Bootstrap 5 via CDN (CSS + JS bundle), renders the navbar partial, the flash partial, then the page content, and is used by all feature views
- [ ] navbar shows 'Login' for guests; for authenticated users shows the user's name, a link to Profile, and Logout; for admins also shows a 'Users' link
- [ ] navbar highlights the active route and is responsive (collapses on small screens via Bootstrap navbar-toggler)
- [ ] flash partial renders success/error/notice messages from Flash and auto-dismisses with Bootstrap alerts
- [ ] views/errors/403.php and views/errors/404.php render styled error pages using the base layout
- [ ] All feature views (login, users index/create/edit, profile show/edit) use the base layout and look consistent and responsive

**Dependencies:** T006, T007

### T009: Security hardening pass

Audit and enforce security practices across the app: CSRF protection on every state-changing request, output escaping everywhere, prepared statements for all DB queries, and a password strength policy.

**Scope:** src/Core/Csrf.php, src/Core/View.php, src/Controllers/AuthController.php, src/Controllers/UserController.php, src/Controllers/ProfileController.php, src/Models/User.php

**Acceptance Criteria:**
- [ ] Every POST/PUT/DELETE form includes a hidden csrf_token field and the corresponding controller action verifies it before mutating state; a bad token aborts with 419
- [ ] All user-controlled output is passed through e()/htmlspecialchars (no raw echo of variables in any view) — verified by grep for `<?= $` patterns and confirming escaping
- [ ] Every database query uses a prepared statement with bound parameters (no string concatenation of user input into SQL) — verified by grep for raw query construction
- [ ] Password policy: minimum 8 characters, enforced on register/create/edit/change-password; rejected attempts show a clear validation error
- [ ] Sessions use a reasonable cookie configuration (HttpOnly, SameSite=Lax) set before session start
- [ ] A short manual or scripted security checklist in the README documents these controls

**Dependencies:** T008

### T010: README & setup docs

Write the README documenting how to install, configure the database, seed the admin user, run the local dev server, and the default credentials plus security notes.

**Scope:** README.md

**Acceptance Criteria:**
- [ ] README.md documents prerequisites: PHP 8.2+, Composer, MySQL
- [ ] Includes step-by-step setup: clone, composer install, copy .env.example to .env and fill DB credentials, import database/schema.sql, run php database/seed.php
- [ ] Documents running the app via `php -S localhost:8000 -t public`
- [ ] States default admin credentials (admin@example.com / password123) with an explicit instruction to change them immediately after first login
- [ ] Includes a security notes section summarizing CSRF, prepared statements, bcrypt hashing, and output escaping
- [ ] Lists available routes (login, logout, users CRUD, profile) and the role each requires

**Dependencies:** T008, T009
