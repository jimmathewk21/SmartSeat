# SmartSeat

SmartSeat is an independent Laravel 12 railway ticket booking application. It
includes working authentication, passenger and administrator roles, railway
catalogue management, train search, group bookings, cancellation and waitlists.
This repository is a source-code skeleton with implemented features, migrations,
tests and original Blade/CSS assets. It contains no operating railway dataset,
default accounts, sample bookings or pre-trained prediction model.

## Railway booking features

- Passenger registration, login/logout and password confirmation through Laravel
  Fortify, with validation, CSRF protection, hashing and login rate limiting.
- Server-side administrator authorization and guarded manual administrator creation.
- Stations, five-digit train numbers, ordered route stops, coaches, berths, travel
  classes, service dates and explicit trip seat-inventory initialization.
- Search by boarding/alighting station and boarding date, including overnight trains.
  Journey times use `Asia/Kolkata`.
- Group reservations for up to six passengers, seat selection, booking history,
  ownership checks, cancellation and lifecycle audit events.

## What makes SmartSeat different

- **Transaction-safe seat allocation:** whole-group allocation uses database
  transactions, ordered locks, uniqueness/foreign-key constraints, bounded retries
  and persistent submission keys. Repeated confirmation does not create another booking.
- **FIFO waitlist promotion:** groups join a queue within a trip/travel class and
  are promoted together after cancellations or inventory initialization. A later
  group cannot bypass an older group whose required seats are unavailable.
- **Historical waitlist confirmation probability:** optional original PHP logistic
  regression learns from immutable snapshots of genuine waitlist history. Chronological
  validation, outcome cutoffs, calibration/performance checks and model versioning
  control publication. No accepted model or insufficient historical coverage produces
  **Prediction unavailable — insufficient historical data.** No percentages are invented.
- **Explainable seat recommendations:** suggestions consider berth preferences,
  group proximity and administrator-verified accessible seats. Reasons are shown;
  passengers explicitly accept suggestions or select seats manually.

The implementation includes transaction safeguards; live MariaDB migration and
parallel booking behavior have **not** yet been verified. SQLite tests cannot prove
MariaDB row-lock or deadlock behavior.

## Technology and prerequisites

- PHP 8.2+ and Composer 2; Laravel 12, Laravel Fortify, Eloquent and Blade.
- Required Laravel PHP extensions, including PDO and the driver for your own database.
  `pdo_sqlite` is required for the isolated integration tests; `pdo_mysql` is needed
  for a MySQL/MariaDB installation. Composer checks PHP dependency requirements.
- SQLite 3.31+ for generated-column support in local development/tests. The schema
  also contains MySQL/MariaDB DDL targeting MariaDB 10.4/InnoDB; live compatibility
  and concurrency results remain unverified. PostgreSQL/SQL Server railway schemas
  are not implemented.
- PHPUnit and Laravel Pint are included as development dependencies.
- Current pages load `public/css/smartseat.css` and original inline SVG directly.
  They need no npm build, external fonts or remote assets.
- An optional starter Vite 7/Tailwind CSS 4/Axios toolchain is retained. Vite 7 requires
  Node.js 20.19+ or 22.12+, as documented in the [Vite 7 guide](https://v7.vite.dev/guide/).

## Install in your own fresh clone

The following are instructions for a new developer's environment. These commands
were **not** executed as part of preparing this source repository. Use only a new,
empty database that you own; never point setup at an existing/shared application DB.

Open a terminal in the cloned project root and install the locked PHP dependencies:

```text
composer install --prefer-dist --no-interaction --no-scripts --no-plugins
```

Create your own `.env` from the safe example. Choose the commands for your shell.
They deliberately refuse to overwrite an existing environment file.

Windows PowerShell:

```powershell
if (Test-Path -LiteralPath .env) { throw '.env already exists; review it locally.' } else { Copy-Item -LiteralPath .env.example -Destination .env }
```

macOS/Linux shell:

```sh
[ ! -e .env ] && cp .env.example .env
```

Review `.env` locally. It must have `APP_ENV=local`, your local `APP_URL` and your own
database settings. `APP_DEBUG=true` is for local development. Never commit actual
credentials or share your generated key. Generate a key only for this fresh clone,
then discover packages:

```text
php artisan key:generate
php artisan package:discover
```

Do not regenerate a key for an existing installation.

## Create your own database and run migrations

For SQLite, leave `DB_CONNECTION=sqlite` and omit `DB_DATABASE` so Laravel uses
`database/database.sqlite`. Create that new, empty file once:

Windows PowerShell:

```powershell
New-Item -ItemType File -Path database/database.sqlite -ErrorAction Stop
```

macOS/Linux shell:

```sh
[ ! -e database/database.sqlite ] && touch database/database.sqlite
```

For your own MySQL/MariaDB database, create a new empty database and a dedicated
restricted account through your normal database administration process. Configure
these values privately in your local `.env`; the empty credential fields below
must be filled locally before connecting:

```dotenv
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartseat_rebuild
DB_USERNAME=
DB_PASSWORD=
```

Do not reuse a root/shared account or any example password. Use `DB_CONNECTION=mysql`
if configuring the MySQL driver; live MySQL compatibility has not been verified.

After checking that the configured target is your **own new empty database**, run:

```text
php artisan migrate
```

This creates the authentication, role, railway, booking/audit and prediction-snapshot
schema from seven migration files. It creates no administrator, passengers, trains,
bookings or training dataset. `DatabaseSeeder` is intentionally empty; no seeding
command is required. Composer install/setup hooks do not run migrations or generate keys.
`composer setup` prints a pointer to this guide; installation is explicit.

### Administrator setup

Registration always creates a passenger. To manage the catalogue, use the existing
manual bootstrap only against your own local MySQL/MariaDB database:

```text
php artisan app:create-administrator --database=smartseat_rebuild
```

The command requires local environment/loopback connection checks, the exact
configured approved database, interactive confirmation and hidden password entry.
It never promotes or overwrites an existing user. The default approved name is
`smartseat_rebuild`; suffixes require an explicit configuration allowlist.
The administrator bootstrap intentionally does not accept SQLite connections.
See [authentication and bootstrap details](docs/authentication.md).

Once an administrator is created, configure stations, trains, route stops, coaches
and berths before scheduling services and initializing inventory. Scheduled train
catalogues are frozen to preserve booking records. No railway records appear automatically.

## Launch the application

After your own environment and database are configured:

```text
php artisan serve
```

Open `http://127.0.0.1:8000` (or your configured local URL). Register a passenger,
log in, and use the catalogue/search/booking pages when railway records exist.
Empty installations display empty states.

The optional Vite toolchain is independent of the current Blade styling:

```text
npm install
npm run build
```

No `package-lock.json` was present in this source workspace, so none was fabricated
or generated during preparation. `npm install` in your clone creates one; review
and retain it before relying on a reproducible npm build. The optional npm build
and its transitive dependency audit have not been validated in this batch.
`composer dev` additionally expects the optional npm tools; `php artisan serve`
is sufficient for the current pages.

## Run safe tests and formatting checks

Install Composer development dependencies, then run from the project root:

```text
php vendor/phpunit/phpunit/phpunit --do-not-cache-result
php vendor/laravel/pint/builds/pint --test
composer --no-plugins --no-scripts validate --strict --no-check-publish
```

`composer test` runs the same isolated PHPUnit suite. Before application providers
boot, the test harness replaces every database connection with SQLite `:memory:`
and checks that no other connection exists. Cache, session and mail use array
drivers; the encryption key exists only in memory. Fixture factories and numerical
learner tests do not create application datasets. The empty-install test applies
all migration definitions only in guarded SQLite memory.

Database-free Windows worker coordination tests use local child processes/loopback
IPC without opening any database. The opt-in scripts under `tests/MariaDb` are not
executed by PHPUnit. Live MariaDB migrations and concurrency tests are optional
separate work requiring an explicitly isolated target; they are **not a prerequisite
for publishing this source skeleton** and have not been executed in this batch.

## Current limitations

- Berths are occupied for the whole trip; segment reuse, railway quotas and partial
  passenger cancellation are not implemented. Group allocation/promotion is atomic.
- No payment gateway, refunds or live railway feed; administrators enter the catalogue.
- Cancellation is an audited record operation, with no fare/refund policy enforcement.
- Password reset, email verification and two-factor authentication are not enabled.
- Waitlist expiry is checked during queue operations; no scheduled expiry sweep exists.
- Prediction quality on real operating history is unmeasured. No trained model or
  historical dataset is shipped, and predictions are withheld without adequate evidence.
- Accessibility flags require physical verification; a lower berth alone is not proof
  of accessibility. Group-proximity suggestions do not guarantee adjacent berths.
- Live MariaDB migration/constraint/concurrency behavior and browser visual/mobile QA
  remain unverified. This repository does not claim production readiness.

## Project guides and source sharing

- [Source layout, exclusions, validation and publishing checklist](docs/source-repository.md)
- [Authentication and local administrator bootstrap](docs/authentication.md)
- [Railway schema, catalogue and search](docs/railway-foundation.md)
- [Booking transactions, cancellation and FIFO waitlists](docs/booking-engine.md)
- [Historical probability and explainable recommendations](docs/intelligence.md)
- [Optional disposable MariaDB test plan](docs/mariadb-approval.md)
- [Windows worker coordination](docs/worker-coordination.md)
- [Locked PHP dependency license inventory](docs/dependency-licenses.md)

Keep `.env`, databases/exports, uploads, private model artifacts, logs, caches,
`vendor`, `node_modules` and local tooling out of the published source. `.gitignore`
does not remove already-tracked files; the maintainer must review the actual release
file set/history before publication. Historical batch reports describe earlier
work, not additional installation requirements.

Setup conventions follow the [Laravel 12 installation](https://laravel.com/docs/12.x/installation)
and [database documentation](https://laravel.com/docs/12.x/database).

## License

Application code follows the existing MIT declaration; see [LICENSE](LICENSE).
Dependencies retain their own copyright and license notices. The source review
inspected no previous project repositories and does not independently certify ownership.
