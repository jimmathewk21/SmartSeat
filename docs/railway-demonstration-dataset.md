# Synthetic Indian railway demonstration dataset

**Step 27C: hardened implementation and offline verification only. No dataset has been installed.** Installation needs separate explicit authorization. Nothing in this feature runs from `DatabaseSeeder`, a migration, a scheduler, or an HTTP route. Existing train demo seeders and their historical-booking blockers are unchanged.

## Dataset definition

All services, station codes, accounts, schedules, reservations and outcomes are synthetic. Geographic labels provide Indian context; this is **not an official Indian Railways station directory, service-number allocation, timetable, or operational dataset**. Stations are named `Mumbai Demo Junction`, etc., with reserved `S27-001`–`S27-100` codes. The existing schema has only `trains.code`, not a dedicated train-number field: the forty unique codes are `S27-90001`–`S27-90040`, and every service name starts with `Synthetic`. The five digits are fictional identifiers within that namespace.

| Records | Exact planned size |
| --- | ---: |
| Stations / trains / route stops | 100 / 40 / 200 |
| Coaches / physical seats | 80 / 640 |
| Completed historical / future scheduled trips | 240 / 60 |
| Trip-seat inventory | 4,800 |
| Fictional passenger / pending owner accounts | 100 / 5 |
| Linked participants / new role-user links | 105 / 105 |
| Confirmed / cancelled bookings | 1,300 / 200 |
| Booking passengers / assignment-history rows | 1,696 / 1,696 |
| Active / released assignments | 1,496 / 200 |
| Final booked / available inventory | 1,496 / 3,304 |
| Waiting / promoted / withdrawn waitlist entries | 100 / 30 / 20 |
| Waitlist passengers | 150 |
| Installation marker in `settings` | 1 |

There are 1,500 **total** bookings, including cancellations and the 30 promoted bookings; promoted bookings are not extra rows beyond that total. Of these, 1,175 belong to historical trips and 325 to future trips. The approximate history target was balanced with future journeys so the search, cancellation and promotion UI can also be demonstrated. A completed journey is represented by a `completed` trip plus its `confirmed` booking; there is no invented `completed` booking status.

Twenty five-stop corridors each have separate outward and return services. The catalog covers Maharashtra, Gujarat, Rajasthan, Delhi, Haryana, Madhya Pradesh, Uttar Pradesh, Bihar, Jharkhand, West Bengal, Assam, Odisha, Andhra Pradesh, Tamil Nadu, Karnataka and Kerala. Each train has a deliberately small C1/CC coach and S1/SL coach with eight seats each; these are demonstration capacities, not claims about real carriage layouts. Each coach has one accessible seat. The catalog source is `database/demo/indian-railway-corridors.php`.

## Determinism and chronology

The default business-data seed is `27001`. An explicit `--as-of=YYYY-MM-DD` date anchors the snapshot at midnight in Asia/Kolkata. The same version, seed, date and application timezone produce the same business rows and SHA-256 fingerprint. A private `Randomizer/Mt19937` instance avoids changing PHP's global random state. Database-assigned IDs depend on existing rows; symbolic references are resolved during installation.

Schedules originate in Asia/Kolkata and are serialized into the configured Laravel application timezone, matching the current datetime columns/casts. The installer rejects a timezone mismatch. Historical trips span roughly 23–210 days before the anchor; future trips run 1–5 and 14–18 days ahead. Physical trains do not have overlapping trips. Accounts/catalog records predate their trips and booking activity.

Bookings include 196 two-passenger groups, other single-passenger bookings, ages 5–82, intermediate origins/destinations, CC/SL assignments and accessibility requests. Booking lead times precede the selected boarding departure. Released assignments retain the actual synthetic cancellation time. Seats are reserved for the **entire trip**; non-overlapping journey segments never reuse an actively reserved seat.

Twenty trips are filled before any waitlist request is made. Thirty distinct booking cancellations each release a seat and trigger exactly one promotion, preserving passenger, owner and journey data. Queue timestamps are distinct and increasing within each trip; earlier eligible requests are not skipped. Withdrawn entries precede promotion events. The remaining 100 entries include 55 on historical trips and 45 on future trips. The application has no expired waitlist status, so historical waiting entries remain `waiting`; derive cutoff eligibility from `boarding_at`, rather than treating these as future eligible requests.

Historical snapshots are installed directly into **new, isolated** records because the live booking service correctly refuses backdated requests. Production services and their clocks are never modified. `RailwayDemoValidator` checks the snapshot before installation: row counts, known columns, references, route order/offsets, trip overlap, journey time snapshots, passenger/seat counts, accessibility, allocation intervals, final pointers, cutoffs, promotion triggers and queue ordering. Keep this validator aligned with future booking invariants.

## Installation safeguards

Installation is restricted to an already provisioned **separate MySQL database named `smartseat_demo`**, with `APP_ENV=local` and explicit `--install --approve-target=smartseat_demo`. `smartseat_dev`, `smartseat_train_test`, production environments, other drivers, and other configured database names are rejected **before connecting**. URL overrides, read/write split keys (even empty or same-target definitions), host arrays, sticky routing, persistent connections, table prefixes and PDO initialization commands are unsupported and rejected. Both the raw configuration and the resolved Laravel connection configuration are checked.

The actual `SELECT DATABASE()` result is verified on the **write PDO**, not a replica. An existing read PDO or transaction is rejected. A private Laravel connection wrapper then pins target verification, advisory-lock acquisition/release, schema reads and the complete insert transaction to that same PDO. It has no read PDO, query event dispatcher or reconnect callback; a lost session fails rather than reconnecting to an unverified target. No database is created and no migration or seeder is invoked.

The target must already contain the retained application/train migrations, one active non-Admin `User` role, and one active non-Admin `Train Owner` role with both existing owner-management permissions. The generator does not create, restore, grant, or modify shared roles/permissions, and never assigns reserved Admin role ID 1. All new passenger participants have `train_access_enabled=true`, `is_approved=false`, and `users.verification_status=false`. All five owners have train access disabled and `train_owner_approved_at=null`; each receives eight newly created trains, but cannot enter the owner panel until an administrator explicitly approves them through Admin → Users. Existing administrator accounts are untouched. The generator does not create an administrator if one is missing.

Preflight requires InnoDB, strict SQL mode, enabled foreign-key/CHECK enforcement, the required columns, unique indexes and composite foreign keys, the stored active-assignment expression, and the enforced inventory status/pointer CHECK. Cross-database foreign keys and custom triggers on written tables are rejected to prevent unintended effects on other data. It never repairs or changes schema. The installer holds a database advisory lock and uses one transaction with no automatic retries. It inserts new records, then finalizes **only the newly inserted inventory rows**. No existing account, station, train, assignment, booking, or permission is updated/deleted.

Any pre-existing `S27-` catalog/booking key or synthetic account address without a matching installation marker aborts installation. The marker is committed with the new records. A rerun with the identical fingerprint returns a no-op: no duplicate rows, password rotation, restoration of changed permissions, or reversal of later application activity. A different seed/date/version is rejected against an installed marker. There is deliberately no reset, delete, refresh, force, or partial-repair mode.

## Preview and later installation

Step 27D preparation may review the source, target schema, role configuration and filesystem ACLs. Dataset installation still needs explicit authorization. Keep this dedicated demo database offline from interactive users during installation. Schema provisioning, administrator provisioning, backups, and any migrations require their own authorization; the command does none of them.

Offline preview (PowerShell; process-only variables, no `.env` edit):

```powershell
$env:APP_ENV = 'testing'
$env:DB_CONNECTION = 'offline_for_preview'
php artisan smartseat:railway-demo --as-of=2026-10-24 --seed=27001
```

Preview shows table sizes, outcomes and the fingerprint without generating credentials or opening a database connection. `bootstrap/app.php` runs `RailwayDemoConsoleBootstrap` after loading configuration (including cached configuration), before registering application/package providers. Only a fresh CLI invocation of the full `smartseat:railway-demo` name overrides Telescope/Pulse to disabled and cache/rate-limiter stores to in-memory arrays. This applies to preview and installation, including rejected installation attempts; unrelated commands and HTTP monitoring keep their normal configuration. No environment/configuration file is changed. The memory cache also avoids the route provider resolving a database cache during console startup.

Use the full command name as shown; do not use command abbreviations, `Artisan::call`, HTTP invocation or a long-lived worker. The command refuses to proceed if telemetry flags are still enabled, but cannot undo provider activity in an already bootstrapped process. Prefer global options after the command (or `--env=local` with `=`). Future providers that eagerly connect to a database or other telemetry integrations need a separate review; this hook specifically covers the current Telescope/Pulse integrations and rate-limiter bootstrap.

Installation example, **only after separate explicit installation approval**:

```powershell
$env:APP_ENV = 'local'
$env:DB_CONNECTION = 'mysql'
$env:DB_DATABASE = 'smartseat_demo'
php artisan smartseat:railway-demo --as-of=2026-10-24 --seed=27001 --install --approve-target=smartseat_demo
```

For a first installation, replace the example date with the current date in Asia/Kolkata. The date is checked again before committing. For an identical rerun, retain the original date and seed; an installed snapshot is not silently moved forward with the clock.

Credentials are documented separately in [development-only credential handling](demo-credentials.md). A caught write/hash/SQL/commit error clears and removes only the reference exclusively created by that attempt. Existing references are never overwritten or removed. Cleanup failure is an explicit installation error, and is not masked by a later lock-release error. A matching installed marker returns a no-op without opening or changing a credential reference.

Filesystem creation and SQL commit are not atomic together. Forced process termination, power loss, inaccessible storage, or an ambiguous commit acknowledgement still require manual review of the marker, namespace and private reference before retrying. A lock-release failure after a successful commit reports that the dataset may already be committed, retaining its reference. Lost credentials cannot be reconstructed by rerunning the generator; any synthetic-account reset requires separate authorization. Never delete history or bypass preflight to recover.

## Algorithm features and limitations

| Feature | Derivation / limitation |
| --- | --- |
| Booking lead time | `boarding_at - created_at`; promotion confirmation lead time differs from original waitlist-request lead time. |
| Booking outcomes and cancellation rate | Booking status, `confirmed_at`, `cancelled_at`, grouped by trip/account/date; 200/1,500 cancellations is a designed fixture ratio, not an empirical population rate. |
| Waitlist outcomes / rank | Status, promotion link/time, and ordering by `created_at,id`. There is no persisted position or complete queue event ledger. |
| Reserved seat occupancy | Inventory status/pointer and unreleased assignment count / trip inventory count. This measures reservations, not observed onboard occupancy or no-shows. |
| Journey segment | Selected stations, ordered route stops, boarding/arrival snapshots. Inventory remains trip-wide. |
| Coach class and seat characteristics | Assigned inventory → seat → coach; berth type and accessibility are present. **Observed class/berth is not a requested preference.** |
| Accessibility demand | Passenger and waitlist request flags, checked against accessible assignments. |
| Preferences / richer outcomes | Requested coach class, berth/window preference, price sensitivity, fares, payments, abandoned searches, no-shows, denial reasons, expiry reasons and detailed event history require a separately reviewed schema/telemetry design. None are invented here. |
| Longitudinal passenger identity | Bookings belong to accounts, which can purchase for others; per-booking passenger rows have no stable cross-booking person identifier. Do not infer one from a repeated fictional name. |

These are small, deliberately simplified corridor graphs and designed outcomes. Transfer/network-routing research needs a richer connected topology. Predictive results on this synthetic sample are not evidence of real passenger behavior. The historical/current queue views do not provide complete event-time labels once later application activity changes records.

## Offline verification

Step 27C permits PHP lint, static checks against migration source, offline unit/subprocess tests and `git diff --check`. Tests exercise in-memory planning/policy/validation, mocked PDOs and private random temporary directories. A fresh Artisan subprocess runs **preview only** with an invalid default connection, MySQL port 1, telemetry initially enabled and a database-backed cache configuration. No installer, database-backed test, migration or seeder is executed. Run them with:

```powershell
$env:APP_ENV = 'testing'
$env:DB_CONNECTION = 'offline_for_unit_tests'
php vendor/phpunit/phpunit/phpunit tests/Unit/Demo
```

Live schema compatibility, actual MySQL rollback/commit behavior, deployment filesystem permissions, installed row counts, account login, owner approval, search and cancellation/promotion integration remain unverified. Offline tests simulate operation failure and credential cleanup; they do not prove a real SQL rollback. No database-backed generator installation test has been run.
