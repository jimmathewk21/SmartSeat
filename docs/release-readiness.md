# Integration review and release readiness

Review date: 31-Oct-2026 (Asia/Kolkata).

Scope note: this is a historical review including production/deployment checks.
Current work prepares a publicly shareable source skeleton; live MariaDB testing
is not a prerequisite for that scope. Use the [source repository guide](source-repository.md)
and README for the current publishing checklist and fresh-clone instructions.

Follow-up: the Windows worker pipe blocker is now addressed and covered by a
database-free process/transport suite. See [worker coordination](worker-coordination.md).
The results and file inventory below describe the original release-review batch;
MariaDB execution still awaits explicit approval and remains unverified.

**Review candidate; not ready for a production-readiness claim.** All safe checks
below pass. MariaDB execution awaits explicit approval, browser visual QA is
incomplete, and the optional starter JavaScript build remains unlocked/unverified.
No existing database, `.env`, APP_KEY or Git state was modified. Sibling projects
were not inspected. No live account, railway records or historical dataset was created.

## Verified defects and changes

1. Array-valued invalid form input was flashed correctly but then passed to Blade
   text attributes, causing HTTP 500 on the validation page. Reproduced on auth,
   catalogue and passenger forms. `FormValue::text` now retains scalar values and
   blanks structured values; Blade still escapes the result. Search fields use the
   same guard. Requests continue rejecting malformed input.
2. Fortify's password-confirmation action accepts a nullable string, while its
   controller passes unvalidated request input. An array produced HTTP 500.
   A bound action subclass validates type/length before delegating credential
   verification to Fortify. Authentication/session behavior is unchanged.
3. Fresh MariaDB station/train CHECK regexes used `$`, which PCRE can match before
   a final newline. They now reject any disallowed character, alongside existing
   length checks. Pure PCRE regression and DDL compilation checks pass; actual
   MariaDB enforcement is still pending. Existing schemas are not modified.
4. Stock Composer setup/create-project hooks could generate/replace a key and run
   migrations against the configured database. Removed those automatic writes;
   setup prints explicit instructions, and test runs isolated PHPUnit directly.
5. README now describes SmartSeat, explicit setup, limitations and release checks.
   Ignore rules cover environment variants, private-key formats, SQL dumps and
   SQLite files. `.env.example` has the SmartSeat name and production HTTPS guidance;
   its APP_KEY field is unchanged/blank. The existing declared MIT license now has
   a root LICENSE file. Locked PHP dependency metadata is inventoried separately.
6. The prepared opt-in MariaDB harness now targets exactly `smartseat_rebuild_test`
   with a dedicated account. A separate guarded fresh-migration script and exact
   approval-only commands are provided. No MariaDB script was executed.

## Integration and security review

An end-to-end HTTP test configures stations, train, route, coach, verified accessible
seats, trip and inventory as an administrator. It logs out, searches, registers
passengers, reserves a two-person group, joins two FIFO groups, retries submissions,
checks foreign-owner denial, cancels as administrator and verifies ordered atomic
promotion and audit attribution. Inventory initialization and cancellation retries
remain idempotent. Optional prediction absence does not break the booking flow.

The retained suite covers registration validation/passenger-only assignment,
login/logout session handling and rate limits, manual administrator safeguards,
catalogue authorization/scoped bindings, trip/seat consistency, booking ownership,
review nonces, request hashing, group rollback, FIFO/accessibility requirements,
recommendation consent and ML publication/abstention. No existing security control
was removed. Administrator training still requires authorization, CSRF, recent
password confirmation, explicit confirmation, throttling and a cache lock.

All mutations use validated input or internal constructed fields; role is not
mass assignable. Booking data is owner/administrator restricted. User passwords
and remember tokens are hidden on serialization. The new helper does not output
raw HTML. Production errors must use APP_DEBUG=false as documented in the
[Laravel deployment guidance](https://laravel.com/docs/12.x/deployment).

Signature scanning of application/configuration/documentation/assets found no
private-key blocks, common credential token signatures or personal local paths.
This is a scoped source check, not a guarantee that no secret exists. Git index,
history, remotes and already-tracked ignored files were not examined because Git
operations are prohibited. Ignore rules do not untrack an already-tracked file.
No sibling-company comparison or independent ownership certification was performed.
Reviewed UI sources use the application's local CSS and original inline SVG.

## Frontend checks

- All Blade files compile under the guarded test application.
- Named Blade routes exist; route names are unique and protected route groups
  retain authentication/administrator middleware.
- Rendered guest/auth/dashboard, catalogue, search, booking/review/cancellation and
  intelligence pages have viewport/main landmarks, unique IDs, labelled controls,
  CSRF fields on mutations, resolvable form/link routes and existing stylesheets.
- Existing empty-state, pagination, escaped-content and malformed-input coverage
  passes. Recommendations require consent and probabilities remain unavailable
  without accepted genuine-history evidence.
- CSS source includes mobile grids, wrapping navigation, horizontal table scrolling,
  visible focus styles, skip navigation and reduced-motion handling.
- **Browser visual QA incomplete.** No approved browser renderer was available;
  desktop/mobile layout, actual keyboard interaction, contrast, zoom and screen
  reader behavior have not been visually/manually verified. Source/render tests
  do not establish these properties.

## Empty installation and test results

FreshInstallationTest applies all seven migration files in order to the verified
SQLite memory connection, invokes the empty seeder, and asserts every application
table contains zero rows. No default administrator, users, railway records,
passengers, bookings, waitlist entries or prediction snapshots are created.
There is no shipped historical training dataset or accepted production model.
Fixtures and learner arithmetic checks run only in disposable test environments.

| Check | Result |
| --- | --- |
| Original baseline before edits | 211 tests, 898 assertions passed |
| Complete PHPUnit suite after edits | **219 tests, 1,832 assertions passed** |
| PHP syntax, app/bootstrap/config/database/routes/tests | **137 source files passed** |
| Laravel Pint across project | Passed |
| Composer strict validation, plugins/scripts disabled | Passed; lockfile consistent |
| Blade compilation, route checks, DOM/form contracts | Passed in PHPUnit |
| Isolated fresh migrations, seeder and integration flow | Passed on SQLite memory only |
| MariaDB migrations/constraints/parallel races | **NOT EXECUTED; approval pending** |
| Browser visual QA | **Incomplete** |
| npm build/lock/transitive license/security review | **Not run; no lockfile** |
| Online dependency vulnerability audit | Not run |

Commands executed for final verification:

```text
php vendor/phpunit/phpunit/phpunit --do-not-cache-result
php vendor/laravel/pint/builds/pint --test
composer --no-plugins --no-scripts validate --strict --no-check-publish
```

PHP `-l` was applied to every source PHP file found under app, bootstrap (excluding
generated cache), config, database, routes and tests. Prepared MariaDB scripts were
compiled only, never run. Composer was available as a PHAR; root version was set
in-process to avoid VCS version discovery. No dependency installation/update ran.

## Release checklist

- [x] PHP dependency lock is present, consistent and unchanged.
- [x] Seven migrations apply in order and create no data on isolated SQLite.
- [x] No automatic administrator, seeding, model training or migration/key hooks.
- [x] Installation/bootstrap/ML/testing instructions and license inventory updated.
- [x] Current pages require no remote assets or npm build.
- [x] Safe tests, syntax, style, route and rendered-view checks pass.
- [ ] Approve and run [the exact MariaDB plan](mariadb-approval.md); inspect actual
      parallel results before asserting MariaDB compatibility/concurrency safety.
- [ ] Complete desktop/mobile visual and keyboard/screen-reader QA.
- [ ] Resolve/lock and verify the optional starter npm pipeline before advertising
      that build as reproducible, or deliberately remove it in a future scoped batch.
- [ ] Run current dependency vulnerability checks and review any advisory findings.
- [ ] Maintainer confirms ownership/licensing and reviews the actual release file
      set/history for secrets using an authorized process before publication.
- [ ] Configure production HTTPS, secure cookies, shared cache/locks, restricted
      database credentials, backups and deployment monitoring in an approved environment.

No commit, push, Git inspection or publication occurred. Next work is approval-based
MariaDB verification and frontend QA, not another feature/schema implementation.

## Files created (11)

- app/Actions/Fortify/ConfirmPassword.php
- app/Support/FormValue.php
- tests/Feature/ReleaseSecurityTest.php
- tests/Feature/FreshInstallationTest.php
- tests/Feature/ReleaseIntegrationTest.php
- tests/Feature/FrontendContractTest.php
- tests/MariaDb/migrate.php
- docs/mariadb-approval.md
- docs/dependency-licenses.md
- docs/release-readiness.md
- LICENSE

## Files modified (20)

- app/Providers/FortifyServiceProvider.php
- app/Support/RailwaySchema.php
- resources/views/auth/login.blade.php
- resources/views/auth/register.blade.php
- resources/views/bookings/passengers.blade.php
- resources/views/trains/search.blade.php
- resources/views/components/railway-fields.blade.php
- tests/Unit/RailwaySchemaCompilationTest.php
- tests/Unit/MariaDbTargetGuardTest.php
- tests/MariaDb/ApprovedTarget.php
- tests/MariaDb/Runtime.php
- tests/MariaDb/worker.php
- tests/MariaDb/run.php
- composer.json
- .gitignore
- .env.example
- README.md
- docs/authentication.md
- docs/intelligence.md
- docs/mariadb-booking-test-plan.md
