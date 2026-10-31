# Public source repository preparation

This guide concerns sharing the implemented source skeleton. It is not a deployment
procedure or permission to run live database tests. Laravel installation steps in
README.md are for each developer's own new environment and database.

## Source layout

| Component | Repository paths | Purpose |
| --- | --- | --- |
| Entry points/bootstrap | artisan, public/index.php, bootstrap/app.php, bootstrap/providers.php | Laravel application, routing and provider registration |
| Configuration | config/, .env.example | Framework settings and safe environment template |
| HTTP/authentication | routes/, app/Http/, app/Actions/Fortify/, app/Providers/, app/Policies/, app/Enums/ | Fortify, validated requests, passenger/admin authorization and booking ownership |
| Railway/booking domain | app/Models/, app/Services/, app/Support/ | Catalogue, search, transactional groups, cancellation, FIFO and safeguards |
| Optional intelligence | app/Services/Intelligence/, app/Services/SeatRecommendations.php | Historical probability, model publication/abstention and explainable suggestions |
| Database definitions | database/migrations/, database/seeders/ | Seven ordered migrations and intentionally empty seeder |
| Test fixtures | database/factories/, tests/Fixtures/ | Explicit disposable fixtures, not operating datasets |
| Frontend | resources/views/, public/css/smartseat.css, resources/css/, resources/js/, vite.config.js | Working Blade pages, original CSS/SVG and optional Vite source |
| Automated checks | tests/, phpunit.xml | Isolated SQLite/unit tests and separately opt-in MariaDB harness |
| Dependency/configuration files | composer.json, composer.lock, package.json | Locked PHP dependencies and retained optional npm configuration |
| Documentation/license | README.md, docs/, LICENSE | Setup, behavior, limits, dependency notices and historical batch reports |
| Writable directory scaffolding | bootstrap/cache/.gitignore and storage/**/.gitignore | Keeps required directories while excluding generated contents |

Laravel 12 middleware configuration lives in bootstrap/app.php and route middleware;
Fortify uses its installed package mechanisms. No additional application middleware
folder, default administrator or seed data is needed to complete the source skeleton.
All application feature implementations and migration definitions were preserved.

## Excluded local material

- Environment variants are ignored; only .env.example is allowed. Its key/credential
  fields are blank and its MySQL/MariaDB section requires private local configuration.
- vendor/, node_modules/, .local/ tooling caches, compiled bootstrap cache, Vite
  output/hot files, logs, sessions, compiled views and test output remain excluded.
- Nested storage ignore files exclude uploads, private model artifacts, predictions,
  local preview files and generated worker files while retaining directory scaffolds.
- Database files, SQL exports, dump/backup files, editor backups, private-key formats
  and common backup/export directories are ignored.
- Dependency installations and ignored runtime files may still exist on the local
  machine. They were not deleted and must not be included in a manual folder upload.
- No npm lockfile existed. It was not generated because the current pages do not
  require the optional build and no npm dependency installation was necessary.

Ignore patterns do not remove already-tracked files or sanitize repository history.
No Git operations, index/history inspection, commits, publishing or repository creation
were performed. The maintainer must review the actual file set/history personally.
Do not upload a ZIP of the whole working directory, including ignored dependencies,
runtime data or .git metadata, as a substitute for reviewing the source file set.

## Focused privacy/security review

The review covers application/configuration/migration/test sources, views/assets,
manifests and documentation inside this workspace. Scans report locations/categories
without printing secret values. Local environment files and ignored private-record
contents are not needed for this source review. No previous repositories or existing
application databases were inspected.

The README and environment example provide no account password or real key. The
optional MariaDB guide no longer contains an executable account-password example
that could be copied literally. A historical report's explicit sibling-repository
names were replaced with a generic scope statement. Test-only password/key literals
and model arithmetic fixtures remain intentional isolated test inputs; the normal
seeder is empty and no historical training data/model is shipped.

Any credential previously shared outside the repository still needs private owner
attention before future operational use. This batch neither reads nor changes local
database passwords/accounts. Source scanning does not establish ownership or provide
a complete secret/vulnerability audit; dependency notices and maintainer review remain
necessary. The application retains its existing MIT declaration.

## Validation

All targeted checks passed on 31-Oct-2026:

| Check | Result |
| --- | --- |
| Default PHPUnit suite | 234 tests, 1,897 assertions passed |
| PHP lint over app/bootstrap/config/database/routes/tests | 141 source files passed |
| Laravel Pint | Passed across the project |
| Composer strict configuration validation | Passed; composer.lock remains consistent and unchanged |
| package.json syntax | Valid JSON; package-lock.json was absent and remains absent |
| Local documentation links | Passed across 15 Markdown documents |
| Required entry points, manifests and directory scaffolds | Present |
| Environment-example sensitive fields | Blank/null; APP_KEY remains blank/unchanged |
| Credential signatures/URLs, private-key signatures, personal paths, prior-repository paths and executable password examples | Zero matches in the reviewed source |
| Credential-style PHP literals | Reviewed locations were isolated tests or hashing metadata |
| Ignore-aware publication candidates | 217 paths; no dependency/tool-cache/runtime/environment/database-export/key artifacts |

These checks use PHP lint, Pint, Composer validation and the default PHPUnit suite.
Framework tests replace all connections with SQLite :memory: before providers boot.
Disposable database fixtures exist only there; no MariaDB scripts run. Database-free
worker tests use local IPC/child processes without a database connection. Composer's
root version was supplied in process to prevent VCS version discovery; no Git command
was used for validation or source enumeration.

No targeted validation failed. npm installation/build, online dependency audit,
browser visual QA, live MariaDB migrations/concurrency and Git index/history review
were not performed. These are limitations to disclose, not claims of passing results.

No migration command, live database write, password/account/grant operation, dependency
installation or Git command is part of this source-preparation batch. The optional
MariaDB migration/concurrency harness and genuine-data ML evaluation remain unverified.

## Maintainer publishing checklist

- [ ] Review the source changes and confirm ownership/licensing and dependency notices.
- [ ] Review the actual publishing file set and history for secrets/private records;
      ignore rules alone cannot establish whether a file was already tracked.
- [ ] Include application/bootstrap/configuration/routes, views/assets, all migrations,
      empty seeder, test factories/tests, Composer manifests/lock, package.json and docs.
- [ ] Include .env.example and nested .gitignore scaffolds; exclude every actual .env,
      credential, database/export, private artifact, dependency directory and runtime file.
- [ ] Preserve composer.lock. If adopting the optional npm build, generate/review its
      lockfile in an appropriate development environment before advertising reproducibility.
- [ ] Review README setup on a fresh clone with the developer's own database. This
      batch validated guarded memory migrations, not a live MariaDB installation.
- [ ] Publish the limitations honestly: no live MariaDB concurrency, real-data ML
      quality or browser visual/mobile verification is claimed.
- [ ] Personally perform any Git/GitHub operations after review. Nothing is published
      automatically by this project-preparation work.

Live MariaDB tests are separate optional engineering work, not a gate for sharing
the source skeleton with these limitations clearly documented.

## Files changed in this batch

- README.md: complete fresh-clone setup, feature/stack overview, own-database migrations,
  launch/testing instructions and current limitations.
- .env.example: local URL and blank-credential guidance for optional MariaDB settings;
  APP_KEY remains blank/unchanged.
- .gitignore: broader environment, key, database/export, backup and output exclusions.
- docs/mariadb-approval.md: generic optional operator scope and removal of the
  executable password placeholder; no database or account change was performed.
- docs/intelligence-batch.md: generic prior-repository scope wording.
- docs/release-readiness.md: distinguish the historical deployment-focused review
  from current public source preparation.
- docs/source-repository.md: this source inventory, review and publishing checklist.
