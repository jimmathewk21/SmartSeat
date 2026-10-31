# Railway foundation batch report

Historical foundation batch report. The current [booking engine guide](booking-engine.md)
and [booking batch report](booking-batch.md) describe subsequent implementation.

## Outcome

The independent railway foundation is implemented. The previous authentication,
roles, administrator bootstrap security and empty seeder remain in place.
No existing MySQL/MariaDB database was contacted or changed; no real records,
application sample dataset, production migrations, administrator command, Git
operations, commits, dependency installations, .env or APP_KEY writes were performed.

## Tables and relationships

Eleven tables/models now cover stations, trains, ordered route stops, coaches,
seats, dated train trips, per-trip seat inventory, bookings, passengers, seat
assignments and waitlist entries. Unique/check constraints and composite foreign
keys enforce catalogue uniqueness and consistent train/trip/seat/passenger
references. Restrictive foreign keys retain booking history. Cancellation and
promotion statuses include matching timestamp checks.

A generated nullable inventory ID with a UNIQUE index permits at most one active
assignment per inventory record, while retaining cancelled assignments. Another
generated unique column prevents multiple active assignments for one passenger.
Waitlists preserve ordered trip/class positions and historical promotion entries.
See [schema details and concurrency contract](railway-foundation.md).

## Administrator tools

Protected, paginated Blade interfaces support explicit station/train creation and
editing, ordered route configuration, coach/seat configuration, trip scheduling
and inventory initialization/overview. Every route is behind authentication and
the administrator Gate; tests cover passenger denial for all 25 catalogue routes.
Scoped nested bindings reject records belonging to a different train.

Form Requests validate inputs, and constraints catch concurrent conflicts.
Multi-record operations use transactions. Catalogue structure freezes once a trip
exists, and referenced stations cannot be edited. No destructive delete actions
are exposed. Reinitializing inventory creates only missing records and preserves
existing blocked/assigned seats and timestamps.

## Passenger search and frontend

The public search page accepts two uppercase station codes and a boarding date.
Results require the correct route order and include matching multiday trips that
start on an earlier date. Only active trains and scheduled trips are listed.
Results show departure/arrival in India time, initialized coaches, and availability
excluding blocked seats and active assignments.

Responsive original Blade pages extend the existing visual design and navigation.
There are explicit empty and uninitialized-inventory states. No purchase or
administrator mutation is exposed to passengers. Whole-trip seat exclusivity is
intentional; segment reuse remains future work.

## Validation

- Full PHPUnit suite: **147 tests, 558 assertions passed** (including the previous
  authentication and administrator-target coverage).
- New cases cover schema constraints, relationships, direct duplicate allocation
  rejection, cross-trip/train rejection, cancellation rollback, ordering, validation,
  scheduling, initialization/idempotency, authorization, search and empty rendering.
- PHP syntax checks: **92 source files passed**.
- Laravel Pint: **passed** for application changes, routes, factories, tests,
  configuration and the railway migration.
- Railway DDL was executed only on isolated SQLite :memory: connections after the
  existing harness removed every other configured database connection.
- Production-style MariaDB/MySQL DDL compilation tests passed. This is SQL
  generation/review coverage, not a test against a MariaDB server.
- Browser visual review was not repeated: Brave access was denied earlier in this
  session. Pages have automated HTTP/Blade rendering coverage.

## MariaDB considerations

The DDL uses InnoDB, explicit/composite foreign keys, CHECK constraints and stored
generated columns with ordinary unique indexes, compatible in design with MariaDB
10.4. Partial unique indexes are not used. SQLite 3.31+ exercises generated-column
constraints; search uses a driver-specific date expression with the same semantics.
MySQL requires enforced CHECK support (8.0.16+). Other drivers/nonempty table
prefixes are unsupported.

SQLite does not reproduce InnoDB row-level locking or deadlocks. A separate,
explicitly authorized disposable MariaDB environment is still required to verify
the migration and concurrent reservation/cancellation/promotion behavior. Existing
databases were intentionally not used for those checks.

## Files created (62)

```text
app/Http/Controllers/Administrator/CatalogueController.php
app/Http/Controllers/Administrator/CoachController.php
app/Http/Controllers/Administrator/InventoryController.php
app/Http/Controllers/Administrator/RouteStopController.php
app/Http/Controllers/Administrator/SeatController.php
app/Http/Controllers/Administrator/StationController.php
app/Http/Controllers/Administrator/TrainController.php
app/Http/Controllers/Administrator/TripController.php
app/Http/Controllers/TrainSearchController.php
app/Http/Requests/Administrator/CatalogueRequest.php
app/Http/Requests/Administrator/CoachRequest.php
app/Http/Requests/Administrator/RouteStopRequest.php
app/Http/Requests/Administrator/SeatRequest.php
app/Http/Requests/Administrator/StationRequest.php
app/Http/Requests/Administrator/TrainRequest.php
app/Http/Requests/Administrator/TripRequest.php
app/Http/Requests/TrainSearchRequest.php
app/Models/Booking.php
app/Models/Coach.php
app/Models/Passenger.php
app/Models/Seat.php
app/Models/SeatAssignment.php
app/Models/Station.php
app/Models/Train.php
app/Models/TrainRouteStop.php
app/Models/TrainTrip.php
app/Models/TripSeatInventory.php
app/Models/WaitlistEntry.php
app/Services/RailwayCatalogue.php
app/Services/TrainSearch.php
app/Support/RailwaySchema.php
database/factories/BookingFactory.php
database/factories/CoachFactory.php
database/factories/PassengerFactory.php
database/factories/SeatAssignmentFactory.php
database/factories/SeatFactory.php
database/factories/StationFactory.php
database/factories/TrainFactory.php
database/factories/TrainRouteStopFactory.php
database/factories/TrainTripFactory.php
database/factories/TripSeatInventoryFactory.php
database/factories/WaitlistEntryFactory.php
database/migrations/2026_10_30_000100_create_railway_foundation.php
docs/railway-batch.md
docs/railway-foundation.md
resources/views/administrator/coaches.blade.php
resources/views/administrator/edit.blade.php
resources/views/administrator/inventory.blade.php
resources/views/administrator/route-stops.blade.php
resources/views/administrator/seats.blade.php
resources/views/administrator/stations.blade.php
resources/views/administrator/trains.blade.php
resources/views/administrator/trips.blade.php
resources/views/components/pagination.blade.php
resources/views/components/railway-fields.blade.php
resources/views/layouts/railway-admin.blade.php
resources/views/trains/search.blade.php
tests/Feature/RailwayManagementTest.php
tests/Feature/RailwaySchemaTest.php
tests/Feature/TrainSearchTest.php
tests/RailwayTestCase.php
tests/Unit/RailwaySchemaCompilationTest.php
```

## Files modified (12)

```text
README.md
app/Models/User.php
config/smartseat.php
docs/authentication-batch.md
docs/authentication.md
public/css/smartseat.css
resources/views/administrator/dashboard.blade.php
resources/views/dashboard.blade.php
resources/views/layouts/app.blade.php
resources/views/welcome.blade.php
routes/web.php
tests/Feature/DashboardTest.php
```

Generated Laravel compiled views/logs may be refreshed under storage during tests.
The prior private authentication previews are not source changes for this batch.

## Remaining work and next batch

Migrations remain unapplied to any real database. No administrator was provisioned.
The batch does not implement booking commands, payment, ticket generation, segment
allocation or cancellation/promotion endpoints. Catalogue versioning would be needed
before allowing structural edits to a scheduled train.

Next, design and implement transactional reservations, cancellation and safe
waitlist promotion, following the locking contract in the foundation guide. Add
real concurrency tests using a separately approved disposable MariaDB instance.
Do not start that next batch automatically.

Suggested commit message (no commit made):

```text
feat: add railway schema, admin catalogue, trip inventory and train search
```
