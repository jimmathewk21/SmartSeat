# Intelligence batch report

## Outcome and safety

Optional waitlist ML, explainable seat recommendations, passenger/admin interfaces
and an opt-in MariaDB parallel-test harness are implemented independently.

No existing database was accessed or modified. No real migrations, real-data model
training, MariaDB concurrency tests, administrator bootstrap, sample railway data,
Git operations, commits, pushes or dependency installations were performed.
.env, APP_KEY and active database connection settings were not modified.

Automated checks use the fail-closed SQLite memory harness, database-free arithmetic
fixtures and fake model storage. Test model artifacts are deleted after pipeline
tests. No production model or fabricated historical dataset is delivered.

## ML pipeline

The original PHP learner is L2-regularized logistic regression, chosen for a small
tabular binary target, probability output and low dependency/runtime cost.
The pipeline includes immutable join-time snapshots, versioned feature extraction,
mature event-based outcomes, chronological splitting with purging, training-only
normalization, evaluation, strict JSON persistence and optional inference.

Defaults require 200 training/100 validation groups, at least 20 of each outcome
in each partition, improved held-out Brier/log loss, calibration ECE at most 0.10
and optimizer convergence. Validation outcomes cannot contribute to fitting.
Prior-rate features use only earlier mature groups known at snapshot capture time.

Artifacts retain algorithm/feature versions, timestamps, split counts, scaling,
weights, training support bounds, metrics and a dataset fingerprint. They contain
no raw training dataset or personal identities. Version files are immutable and
the active pointer is atomically replaced with a checksum.

No real-world accuracy, calibration or confirmation rate has been established.
Arithmetic unit fixtures verify the mechanics only.

## Prediction behavior and interfaces

Without sufficient genuine data and an accepted trained model, the exact message is:

Prediction unavailable — insufficient historical data.

Missing/corrupt/incompatible/stale models, missing snapshots and uncovered feature
states abstain rather than invent a percentage. Accepted estimates are identified
as historical estimates from join state, not guarantees. Extreme values display
bounded ranges rather than rounded certainty. Waitlist request numbers and booking
status remain visible.

The administrator-only Prediction models page shows availability/split counts,
model version/time and held-out metrics/bins. Explicit training requires CSRF,
recent password confirmation, an explicit checkbox, throttling and a cache lock.
Startup, booking and GET requests do not train. Failed retraining preserves the
accepted model. Training was not triggered against any existing database.

## Recommendation behavior

A deterministic greedy group allocator uses explicit berth preferences, verified
accessible-seat requirements, soft senior convenience and same-coach proximity.
It prioritizes required accessible passengers, avoids consuming scarce verified
accessible seats unnecessarily, and breaks ties deterministically.

Accessibility defaults to unknown and must be explicitly verified by an administrator.
Age never creates an accessibility requirement or medical inference. No diagnoses
are collected. Impossible verified-capacity requests are rejected.

Every suggestion has reasons and an explicit Use these suggestions for review action.
Manual selections are never replaced automatically. Soft preferences can be
overridden by explicit choices; hard accessibility requirements are validated again.
Availability is checked at transaction commit. Existing FIFO, whole-group, whole-trip
occupancy, uniqueness and FK protections remain. Automatic promotion now honors
stored accessibility/berth/group preferences under the same locks. Legacy default
submission hashes remain compatible.

## Verification

- PHPUnit: **211 tests, 898 assertions passed**, including previous booking,
  cancellation, FIFO, authorization, idempotency and schema coverage.
- New tests cover empty/insufficient history, join snapshots, time-cutoff leakage,
  chronological purging, mathematical fitting, scaling, metrics/calibration,
  persistence/versions, stale/incompatible/uncovered predictions, ranking,
  accessibility, senior/explicit preferences, grouping, reasons, consent, manual
  selection, preference-aware promotion, ML admin controls and password confirmation.
- **130 PHP source syntax checks passed**, including the prepared MariaDB scripts.
- Laravel Pint: **passed** for application/tests/routes/changed configuration and
  the additive migration.
- Blade pages have automated rendering coverage. Browser visual review remains
  unperformed following the earlier access denial.

## MariaDB readiness

tests/MariaDb is excluded from the default Unit/Feature suite. Its pure target
guard is unit tested; the subprocess harness is prepared/syntax checked only.

It requires an exact explicitly approved disposable local target, restricted
non-root credentials through stdin, MariaDB 10.4 identity verification, a complete
pre-provisioned schema and zero existing application records. It contains no DB
creation, migration or cleanup operation. ML history capture is disabled for its
disposable fixtures.

Independent worker connections synchronize at actual transaction-begin barriers
and report overlapping transaction timing. Prepared cases cover same-seat claims,
concurrent cancellation/multiple promoters, duplicate submissions, allocation
rollback, injected engine retry, direct uniqueness enforcement and a real opposing-
lock deadlock probe. Faults must actually be observed to pass. Credentials are not
written to arguments or reports. Fixtures remain for review; cleanup needs separate
authorization.

**None of those MariaDB scenarios has been executed.** Parallel behavior, platform
process handling, live DDL and InnoDB isolation/deadlocks remain unverified.
See [the reviewed plan](mariadb-booking-test-plan.md) and
[harness prerequisites](intelligence.md#mariadb-concurrency-preparation).

## Files created (28)

```text
app/Http/Controllers/Administrator/IntelligenceController.php
app/Http/Requests/Administrator/TrainWaitlistModelRequest.php
app/Models/WaitlistPredictionSnapshot.php
app/Services/Intelligence/FeatureVector.php
app/Services/Intelligence/LogisticRegression.php
app/Services/Intelligence/PredictionMetrics.php
app/Services/Intelligence/WaitlistDataset.php
app/Services/Intelligence/WaitlistFeatures.php
app/Services/Intelligence/WaitlistHistory.php
app/Services/Intelligence/WaitlistModels.php
app/Services/Intelligence/WaitlistPrediction.php
app/Services/Intelligence/WaitlistTraining.php
app/Services/SeatRecommendations.php
database/migrations/2026_10_30_000300_add_intelligence_foundation.php
docs/intelligence-batch.md
docs/intelligence.md
resources/views/administrator/intelligence.blade.php
tests/Feature/SeatPreferenceBookingTest.php
tests/Feature/WaitlistIntelligenceTest.php
tests/MariaDb/ApprovedTarget.php
tests/MariaDb/ParallelWorkers.php
tests/MariaDb/Runtime.php
tests/MariaDb/run.php
tests/MariaDb/worker.php
tests/Unit/LogisticRegressionTest.php
tests/Unit/MariaDbTargetGuardTest.php
tests/Unit/SeatRecommendationsTest.php
tests/Unit/WaitlistPipelineTest.php
```

## Files modified (24)

```text
README.md
app/Http/Controllers/BookingController.php
app/Http/Controllers/BookingWizardController.php
app/Http/Requests/Administrator/SeatRequest.php
app/Models/Booking.php
app/Models/Passenger.php
app/Models/Seat.php
app/Services/BookingEngine.php
app/Support/BookingRules.php
config/smartseat.php
docs/booking-batch.md
docs/booking-engine.md
docs/mariadb-booking-test-plan.md
docs/railway-foundation.md
public/css/smartseat.css
resources/views/administrator/dashboard.blade.php
resources/views/administrator/seats.blade.php
resources/views/bookings/passengers.blade.php
resources/views/bookings/seats.blade.php
resources/views/bookings/show.blade.php
resources/views/components/railway-fields.blade.php
resources/views/layouts/railway-admin.blade.php
routes/web.php
tests/RailwayTestCase.php
```

Ordinary compiled views/logs and fake testing storage may be refreshed by tests.
No code/assets from previous repositories were inspected or copied.

## Limitations and next batch

Genuine historical data is still needed before publishing any real model.
Join-state estimates, voluntary cancellations, rare accessibility cohorts, per-train
heterogeneity, distribution drift and small calibration bins limit reliability.
Feature-range abstention is not proof of joint-distribution coverage. Recommendation
ranking is heuristic; seat-number proximity is not guaranteed physical adjacency,
and software cannot verify real-world accessibility.

Next, obtain explicit approval for the disposable MariaDB target/processes and run
the prepared concurrency suite, complete desktop/mobile QA, review privacy/retention
and gather genuine mature history. Train only with separate authorization and
evaluate actual held-out performance before relying on estimates.

No automatic training, deployment, payments or further feature batch was started.

Suggested commit message (no commit made):

```text
feat: add optional waitlist ML, explainable seat suggestions and concurrency test harness
```
