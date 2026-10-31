# Authentication and access

SmartSeat is an independent Laravel 12 application. Authentication uses the installed
Laravel Fortify controllers, requests, session guard and authentication pipeline,
following the [official Laravel 12 Fortify conventions](https://github.com/laravel/docs/blob/12.x/fortify.md).
No other project's code, templates or assets are needed.

## Current features

- Registration creates passengers only, regardless of submitted role fields.
- Emails are trimmed and stored in lowercase, with a unique database index.
  Login uses a Fortify request subclass to validate email format and normalize
  string input safely. Fortify's pre-validation lowercasing is disabled so array
  input returns validation errors rather than a server error.
- Passwords require 12–72 characters, uppercase and lowercase letters, a number,
  a symbol and matching confirmation. The bcrypt limit of 72 bytes is also checked
  for multibyte input; null bytes are rejected. Laravel's `hashed` model cast hashes
  passwords. Sensitive input arrays are excluded from PHP exception stack arguments.
- Login uses Fortify validation and throttling: five attempts per email/IP per
  minute, plus thirty per IP per minute. Cache storage must be shared when running
  multiple application instances.
- Fortify regenerates sessions on login and registration, and invalidates sessions
  and rotates CSRF tokens on logout. Logout requires POST with a CSRF token.
- Password confirmation is available at `/user/confirm-password`.
  Its bound Fortify action validates password input before credential checking;
  malformed structured input produces a validation error rather than HTTP 500.
- Web middleware supplies CSRF protection. Session cookies retain Laravel's
  HttpOnly and SameSite defaults. Configure HTTPS/secure cookies when deploying.
- Password reset, email verification, profile updates, two-factor authentication
  and passkeys are not enabled in this batch.

## Roles and routes

`App\Enums\UserRole` defines `passenger` and `administrator`. The user model and
the new role migration both default to passenger. `role` is not mass assignable.
Privileged code must assign the enum explicitly; no HTTP role assignment exists.

| Named route | URL | Access |
| --- | --- | --- |
| `home` | `/` | Public landing page |
| `login` / `login.store` | `/login` | Guests |
| `register` / `register.store` | `/register` | Guests; creates passengers |
| `logout` | `/logout` | Authenticated POST |
| `dashboard` | `/dashboard` | Authenticated; administrators redirect to their overview |
| `administrator.dashboard` | `/administrator` | `auth:web` and `can:access-administrator` |
| `password.confirm` / `password.confirm.store` | `/user/confirm-password` | Authenticated |

Guests are redirected to login. Passengers receive HTTP 403 on administrator
routes. Add both authentication and the Gate middleware to future admin routes;
hiding navigation links does not authorize a request. Add `password.confirm`
middleware to future sensitive operations that require recent reauthentication.

## Administrator bootstrap (manual operation only)

The command is implemented but was not executed during this batch. It never runs
on application startup, registration, seeding or migration.

Before an operator uses it, review the connection configuration and arrange the
necessary migrations on an explicitly authorized local rebuild database. The
new migration only adds `users.role`; existing Laravel migrations already provide
the authentication tables. No migrations were run by this implementation.

For a future authorized manual invocation:

```text
php artisan app:create-administrator --database=smartseat_rebuild
```

The command requires interactive input and explicit confirmation (default: no).
It verifies the target before collecting account details. Name and email are
prompted; passwords and confirmation are hidden and never accepted as arguments
or options. If hidden input is unavailable, it fails rather than showing input.

`AdministratorTarget` requires the local environment, an exact approved database
name, a matching configured database and a local MySQL/MariaDB host. It rejects
connection URLs, Unix sockets and read/write splits, then checks `SELECT DATABASE()`
on the write connection. It never creates or changes a database. The command name
must immediately follow `artisan`; programmatic Artisan calls and other commands
are intentionally refused by the existing invocation safeguard.

The base name is exactly `smartseat_rebuild`. Optional suffixes must be explicitly
listed in `config/smartseat.php` under
`administrator_bootstrap.permitted_database_suffixes`; the default list is empty.
Suffixes contain lowercase letters/digits and single separating underscores.
Wildcards, uppercase, leading/trailing underscores and `smartseat_dev` are rejected.

The insert-only action validates uniqueness and password strength, hashes the
password via the model cast, and assigns the administrator enum. It never promotes
or overwrites an existing user; the unique email index also protects concurrent
inserts. Do not expose this internal action through HTTP endpoints.

## Pages and assets

Blade layouts provide consistent navigation, validation messages, accessible form
labels, CSRF tokens, escaped user content and mobile layouts. Original styling is
in `public/css/smartseat.css`; the landing illustration is an inline SVG written
for this application. There are no remote fonts, inherited assets or build steps
required for these pages. Passenger and administrator dashboards show empty states
and issue no railway queries. The [railway foundation](railway-foundation.md) now
provides separate catalogue and search pages. The [booking engine](booking-engine.md)
adds authenticated reservations, waitlisting and cancellation; real payments are excluded.

`App\Support\FormValue::text` protects text attributes when displaying flashed
invalid input: scalar values are retained and structured input is blanked. Blade
still escapes the result. See the [integration/release review](release-readiness.md).

## Testing safely

```text
php vendor/phpunit/phpunit/phpunit --do-not-cache-result
```

`tests/TestCase.php` replaces all database connection configuration before
application providers boot. Only SQLite `:memory:` remains; a safety assertion
checks the driver, database and absence of connection URLs and other connections.
Session/cache/mail use array drivers and an ephemeral in-process encryption key;
`.env` and APP_KEY are never written. PHP needs `pdo_sqlite` for integration tests.

`DatabaseTestCase` creates only a disposable users fixture table, uses
`DatabaseTransactions`, and never runs migrations or seeders. Factories create
fixtures solely in this isolated memory database. Public pages, dashboard views,
role checks and target safeguards have database-free coverage. Target identity
queries use mocks only; the administrator command is not executed by tests.

Railway integration tests use an additional RailwayTestCase to apply only the
railway migration to the verified SQLite memory connection. Authentication tests
retain their minimal fixture schema. No production migration command is invoked.

Next, verify the booking engine on an explicitly approved disposable MariaDB
instance before relying on its locking behavior in a deployment.
