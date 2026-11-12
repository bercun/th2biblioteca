# AGENTS.md

## Overview & Stack

- **Stack**: Pure PHP (8.x typed properties, PDO MySQL) backend with vanilla JavaScript (ES6+), HTML5, and CSS3.
- **Dependencies**: Zero package managers (no Composer, no npm, no node_modules). Do not run or look for `npm` or `composer` scripts.
- **Root Entrypoint**: `index.php` acts as the single Front Controller for the public library app and all REST API endpoints.
- **Admin Entrypoint**: `admin/index.php` serves the admin panel UI directly, communicating with the API via `index.php?route=admin/...`.

## Running the Application

To run locally using PHP's built-in web server from the repository root:

```bash
php -S localhost:8000
```

- Do NOT pass `index.php` as the router script: with it, every request (CSS/JS, `admin/index.php`) returns the home page. Verified.
- Public library: `http://localhost:8000/` or `http://localhost:8000/index.php`
- Admin dashboard: `http://localhost:8000/admin/index.php`
- API endpoints: `http://localhost:8000/index.php?route=<route_name>` or `http://localhost:8000/index.php/<route_name>`

## Database Setup & Configuration

- **Connection**: Managed as a PDO singleton in `config/connection.php`.
- **Precedence**: Environment variables > `config/db.local.php` (git-ignored) > default fallback values.
  - Env vars: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
  - Local config file (`config/db.local.php`):
    ```php
    <?php
    return [
        'host' => 'localhost',
        'port' => '3306',
        'dbname' => 'biblioteca',
        'username' => 'root',
        'password' => '',
    ];
    ```
- **Schema & Migrations**: SQL files live in `bd/`.
  - Base schema: `bd/biblioteca.sql`
  - Supplemental migrations applied over time:
    - `bd/add_admin_role.sql`
    - `bd/add_books_isbn.sql`
    - `bd/add_book_price.sql`
    - `bd/add_reviews.sql`
    - `bd/add_user_books_purchased_at.sql`
    - `bd/migrate_books_category.sql`

## Architecture & Code Conventions

### Routing (`index.php`)
- Routes are explicitly defined in `$routes` array in `index.php`.
- Adding a new endpoint requires registering it in `index.php`.
- Default route or empty route serves `front/index.html` with dynamic base path injection via `basePath()` from `config/paths.php`.
- **Important Gotcha**: `dispatchController()` executes `chdir(dirname($controller))` before including the controller file and restores the CWD afterwards. Controllers can use relative `require_once` to neighboring files and `../../models/` directly.

### Controller Pattern (`controllers/`)
- Organized into subdirectories:
  - `controllers/users/`: `register.php`, `login.php`, `logout.php`, `current.php`
  - `controllers/books/`: `search.php`, `favorites.php`, `buy.php`, `review.php`
  - `controllers/admin/`: `books_list.php`, `books_create.php`, `books_update.php`, `books_delete.php`, `users_list.php`, `users_purchases.php`
  - `controllers/admin/book_image.php` and `require_admin.php` are helpers included by other controllers, not routed endpoints.
- Responses must return JSON with `header('Content-Type: application/json; charset=utf-8')` and a JSON structure containing `['success' => bool, ...]`. `users/current.php` is an exception: it returns the user object or `null`.
- Admin controllers require `require_once __DIR__ . '/require_admin.php'` near the top, which verifies active session and `role === 'admin'`.
- Admin book create/update accept `multipart/form-data` (`$_POST` + `$_FILES['imageFile']`), which is what `admin/admin.js` sends; other endpoints read JSON from `php://input`.

### Data Models & Repositories (`models/`)
- Entities represent DB records (e.g., `User.php`, `Book.php`, `Review.php`, `UserBook.php`, `BookCategory.php`).
- Database access belongs in Repository classes (`RepoUser.php`, `RepoBook.php`, etc.).
- Repositories accept an optional PDO instance in constructor (defaults to `getConnection()`).
- Always use prepared statements with parameter binding for SQL queries.

### Frontend (`front/` and `admin/`)
- API route constants and base path resolution are in `front/js/const.js` (`BASE_PATH` is dynamically calculated from `document.currentScript.src`).
- `front/js/utils.js` holds only card rendering (`createBookCardElement`), `setContainerMessage`, and `formatDateTime`. Script load order in `front/index.html` matters: `const.js` must load before scripts that use `API`.
- `README.md` documents the full architecture and function reference; keep it in sync when routes or schema change.
- Book covers are stored under `front/assets/images/`.

## Verification & Testing

There is no automated test runner configured in this repo. Verify changes via:
- PHP syntax checks on modified files:
  ```powershell
  php -l <path-to-file.php>
  ```
- Batch syntax linting of all PHP files:
  ```powershell
  Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
  ```
