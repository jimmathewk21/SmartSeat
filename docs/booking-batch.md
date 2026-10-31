# Booking engine batch report

Historical booking batch report. See the current [intelligence report](intelligence-batch.md)
for ML, recommendation and prepared parallel-test work.

## Delivered workflow

Authenticated passengers select a trip and route stations, enter up to six passengers,
select distinct seats/coaches in one class or request a waitlist place, review the
server-stored group and confirm. UUID references, confirmation/details, owner-scoped
paginated history and a cancellation confirmation page are implemented. Empty
history/inventory and waitlist status have clear states.

Details and final confirmation revalidate the journey, actual India-time boarding
departure, route order, class, configured capacity, passenger fields and availability.
No real payment processing, machine learning or seat recommendations were added.

## Concurrency mechanisms

BookingEngine uses transactions, a consistent train-then-trip locking protocol,
deterministic child/inventory ordering and bounded three-attempt deadlock handling.
The original active-assignment unique indexes, composite foreign keys and CHECK
constraints remain intact. Unique owner/submission keys plus canonical request hashes
prevent duplicate confirmations; matching retries return the original booking.
Owner-bound expiring session drafts and review-version tokens prevent draft IDOR
and stale-tab confirmation. Confirmation uses server-stored reviewed data.

Allocation, passenger/group state and audit events commit or roll back together.
Known duplicate/deadlock/busy conflicts return validation feedback after rollback;
nested deadlocks propagate to the outer transaction for whole-operation retry.
Inventory initialization participates in that outer retry protocol.

These are implemented defenses, not a claim of fully verified MariaDB concurrency.
Only isolated SQLite transactions were executed. No genuine parallel MariaDB
transaction was run; the separate test plan requires explicit target approval.

## Cancellation and FIFO policy

Only the booking owner or an authorized administrator can view/cancel details.
Whole-booking cancellation preserves history, timestamps all passengers, cancels
active assignments/waiting entries and promotes eligible groups in the transaction.
Repeated cancellation does not release seats again or create duplicate audit events.
Blocked inventory remains blocked.

Waitlists are per trip/class, ordered by creation time then unique ID. Historical
request numbers remain unique and monotonically allocated. Promotion is all-or-nothing;
a later smaller group or direct reservation cannot skip a waiting head group.
Oversized groups that exceed physical class capacity are rejected.

Cancellation and inventory initialization trigger promotion; booking attempts first
process earlier groups. Promotion records assignments, confirmed passenger/group
state, promoted timestamps and activity events atomically. Departed boarding requests
expire when the queue is processed. There is no scheduled expiry sweeper.

booking_events records created/confirmed/waitlisted/promoted/cancelled/expired status
changes and their initiating actor where applicable. It has no editing/deletion API.

## Journey segments and policy limits

A seat is exclusive for its entire train trip, regardless of boarding/alighting
stations. No segment-based reuse or overlapping occupancy scheme was introduced.
Cancellation applies to the entire group, not individual passengers. After-boarding
cancellation is an audited record operation in this release; no refund/cutoff policy
is implemented. Trip-wide cancellation/notification policy remains separate work.

## Verification

- Full PHPUnit suite: **187 tests, 786 assertions passed**, retaining previous
  authentication, administrator-security, catalogue and schema coverage.
- New coverage: successful/multi-passenger booking, validation, stale/cross-trip/
  wrong-class seat claims, partial allocation rollback, injected duplicate-key errors,
  persistent submission uniqueness/idempotency, owner/admin authorization, CSRF,
  draft ownership/expiry/review tokens, cancellation/double cancellation, promotion
  rollback, FIFO time/ID ordering, whole-group priority, expired requests, whole-trip
  occupancy, empty inventory/history, initialization promotion and pagination.
- **105 PHP source syntax checks passed.**
- Laravel Pint: **passed** for application files, routes, changed configuration,
  tests and the additive migration.
- Blade/HTTP flows rendered through automated tests. Browser visual review was not
  performed because access was denied earlier in this session.
- No MariaDB database or connection was created/accessed. No real migrations,
  bookings, passengers, sample dataset, administrator bootstrap, dependencies,
  .env/APP_KEY changes, Git operations, commits or pushes were performed.

The additive migration was applied only inside the verified SQLite :memory: harness.
All real-database migrations remain unapplied. See [the engine guide](booking-engine.md)
and [unexecuted MariaDB concurrency test plan](mariadb-booking-test-plan.md).

## Files created (24)

```text
app/Http/Controllers/BookingController.php
app/Http/Controllers/BookingWizardController.php
app/Http/Requests/BookingDetailsRequest.php
app/Http/Requests/BookingReviewRequest.php
app/Models/BookingEvent.php
app/Policies/BookingPolicy.php
app/Services/BookingDrafts.php
app/Services/BookingEngine.php
app/Support/BookingRules.php
database/migrations/2026_10_30_000200_add_booking_submission_and_audit.php
docs/booking-batch.md
docs/booking-engine.md
docs/mariadb-booking-test-plan.md
resources/views/bookings/cancel.blade.php
resources/views/bookings/index.blade.php
resources/views/bookings/partials/journey.blade.php
resources/views/bookings/passengers.blade.php
resources/views/bookings/review.blade.php
resources/views/bookings/seats.blade.php
resources/views/bookings/show.blade.php
resources/views/layouts/booking.blade.php
tests/BookingTestCase.php
tests/Feature/BookingEngineTest.php
tests/Feature/BookingWorkflowTest.php
```

## Files modified (18)

```text
README.md
app/Http/Controllers/Administrator/CatalogueController.php
app/Models/Booking.php
app/Providers/AppServiceProvider.php
app/Services/RailwayCatalogue.php
config/smartseat.php
docs/authentication.md
docs/railway-batch.md
docs/railway-foundation.md
public/css/smartseat.css
resources/views/administrator/dashboard.blade.php
resources/views/dashboard.blade.php
resources/views/layouts/app.blade.php
resources/views/trains/search.blade.php
resources/views/welcome.blade.php
routes/web.php
tests/Feature/DashboardTest.php
tests/RailwayTestCase.php
```

Compiled views and ordinary runtime logs under storage may be refreshed by tests.
No other project was inspected or copied.

## Remaining risks and next batch

Before deployment, execute the approved disposable MariaDB 10.4 migration/concurrency
plan with independent processes, barriers and committed-state assertions. SQLite
does not reproduce InnoDB lock waits, deadlocks or isolation semantics. Also perform
desktop/mobile browser QA and review privacy retention and cancellation policy.
The conservative train-level mutex and strict group FIFO may limit throughput or
leave free seats temporarily unused; those are explicit correctness tradeoffs.

The recommended next batch is verification and policy hardening, not automatic
payment, notification, machine-learning or recommendation implementation.

Suggested commit message (no commit made):

```text
feat: add transactional group bookings, cancellation and FIFO promotion
```
