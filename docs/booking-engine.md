# Booking engine

The [intelligence module](intelligence.md) adds optional validated ML estimates,
consent-based seat suggestions and persisted accessibility/berth preferences.

SmartSeat supports authenticated group reservations, waitlisting and whole-booking
cancellation. Code and assets are independently written. No existing database,
administrator bootstrap, production migration, .env, APP_KEY or Git state was touched.
Executable database checks use only the isolated SQLite memory test harness.

## Passenger workflow

1. Find a train and choose Book or join waitlist. Authentication is required.
2. Enter up to six passengers, their names/ages/genders, stations and one travel class.
3. Choose a coach and distinct seat for each passenger, or review a waitlist request.
   A group may select multiple coaches in the same class.
4. Review the complete group, journey and seats. Seats are not held at review.
5. Confirm. The server rechecks the trip, route, actual boarding departure and every
   seat, then records the entire group atomically. No payment is collected.
6. Open My bookings for confirmation, passenger/seat details, waitlist status,
   activity history and whole-booking cancellation.

Dates/times use the foundation's India-time boarding semantics. Departed boarding
stops, inactive trains, cancelled trips, invalid routes and wrong trip/class seats
are rejected. Waitlisting requires insufficient free seats or an earlier waiting
group. A group larger than the class's total configured capacity is rejected because
it could never fit. Classes with no configured seats cannot be reserved/waitlisted.

The application does not collect fares or perform payments/refunds. total_paise
remains zero and is not presented as an actual priced ticket.

## Authorization and drafts

All routes require auth:web and normal web CSRF middleware. History is owner-scoped.
BookingPolicy permits only the owner or an administrator to view/cancel details.
Public URLs use random UUID references; explicit policy checks still apply.

Drafts are owner-bound server-session records with a 30-minute lifetime and a
ten-draft cap. Personal details do not appear in URLs. New drafts prune expired
ones; logout invalidates the session. Review data is stored server-side. A review
version token prevents an old tab confirming a selection changed in another tab.
Submitted passenger, owner, seat or class fields on the final POST are ignored.

The draft UUID becomes a persistent submission key. UNIQUE (user_id, submission_key)
and a canonical request hash protect against duplicate HTTP/process confirmations.
The same key/payload returns the same booking, even after cancellation; changed
payloads are rejected. A new independently created draft represents a new request,
not a global name-based duplicate detector.

## Transactions and locking

BookingEngine uses Laravel transactions with three configured deadlock attempts,
following [Laravel 12 transaction conventions](https://github.com/laravel/docs/blob/12.x/database.md#handling-deadlocks)
and [pessimistic locking](https://github.com/laravel/docs/blob/12.x/queries.md#pessimistic-locking).

Lock order is train, then trip, matching catalogue/initialization operations and
avoiding train FK/trip lock inversion. Child bookings/passengers/inventory are then
locked in deterministic order. The train/trip mutex serializes allocation, queue
and cancellation decisions for that train. This conservative scope limits throughput.

Cancellation resolves trip metadata before starting its transaction, avoiding an
InnoDB consistent-read snapshot before the parent locks. Eligibility uses the locked
current train/trip. Inventory locks and writes use ascending inventory ID order;
automatic promotion now matches verified accessibility requirements and soft berth/
group preferences, with deterministic inventory-ID tie breaking.

Original active-inventory/active-passenger UNIQUE indexes remain the final defense
against duplicate assignment. Composite FKs still enforce matching train/trip
references. No original CHECK, FK or unique guarantee was weakened. Failures roll
back the whole group and its audit events.

Known duplicate/deadlock/busy errors produce retryable validation feedback after
rollback. Nested deadlocks propagate to the outer transaction for whole-operation
retry; inventory initialization has bounded outer retries too. After key conflicts,
the engine reads the writer to resolve a committed matching request. Unexpected DB
errors are never treated as a successful booking.

## FIFO waitlist and group policy

FIFO is per trip/class, ordered by creation time then unique entry ID. Position is
a unique monotonically allocated historical request number, not a dynamically
renumbered queue rank. The generated active-passenger UNIQUE column prevents
duplicate waiting entries for the same passenger/journey.

Promotion is all-or-nothing for a group. Later smaller groups and direct reservations
cannot bypass a waiting head group. Seats may be assigned in any coach of that class.

Cancellation and inventory initialization invoke promotion inside their transaction.
Booking attempts first process earlier eligible groups. Passenger status, assignments,
promoted timestamps and audit events commit together. Reprocessing does not promote
an already processed group. Departed boarding requests expire with a cancellation
timestamp and expired activity event instead of receiving seats.

There is no scheduled expiry sweep: expiry is processed when the queue next runs.
Future capacity or blocked-inventory controls must invoke promoteTrip under the same
transaction/lock protocol when they make seats available.

## Cancellation and audit

Cancellation affects every passenger; individual passenger cancellation is excluded.
It timestamps booking/passenger cancellations, cancels active assignments and waiting
entries, preserves history and promotes eligible groups atomically. Blocked inventory
flags are preserved. A second cancellation neither releases promoted seats nor adds
another cancellation event.

Cancellation after boarding is allowed as an audited record operation in this
release. New reservations/promotions still reject departed boarding journeys.
Refund/cutoff policy and trip-wide cancellation/notifications are separate work.

booking_events records created, confirmed, waitlisted, cancelled, promoted and expired
transitions, old/new status, timestamps and the initiating actor. Automated promotion/
expiry uses a NULL actor. There is no event-edit/delete endpoint.

## Journey segments

A seat remains exclusive for the entire train trip. Boarding/alighting stops validate
the route and determine times/eligibility; they do not release a seat for another
segment. One active assignment per trip-seat inventory record remains enforced.
Segment reuse requires a different occupancy schema and overlap/concurrency controls
and was intentionally not introduced.

## Schema and safe tests

The additive 2026_10_30_000200_add_booking_submission_and_audit.php migration adds
submission/class metadata, a FIFO lookup index and booking_events. Existing rows
may retain NULL submission metadata; new engine bookings always populate it.
No real database migration was executed.

Run the normal suite:

```text
php vendor/phpunit/phpunit/phpunit --do-not-cache-result
```

RailwayTestCase applies foundation/additive migrations only after the existing
harness verifies SQLite :memory: and removes every other connection. Factories are
disposable fixtures, not a railway dataset or seeder. Current results/file inventory
are in the [batch report](booking-batch.md).

SQLite verifies state, constraints, rollback and HTTP/security flows; it cannot
verify parallel InnoDB locking or deadlock semantics. No MariaDB server was contacted.
The [MariaDB test plan](mariadb-booking-test-plan.md) is prepared but unexecuted and
requires explicit target approval. Browser visual QA remains unperformed following
the earlier browser-access denial.

Before deployment, complete that plan and desktop/mobile visual QA, review privacy/
cancellation policy and separately approve schema application. Payments remain
excluded. Optional ML and recommendations are described in the intelligence guide.
