# Railway foundation

The [intelligence module](intelligence.md) adds explicit seat-accessibility metadata,
passenger preferences and immutable historical snapshots without weakening this schema.

The [booking engine](booking-engine.md) now builds on this foundation. An additive
migration adds submission deduplication, travel class and a booking activity table.

SmartSeat's railway code is independently written in this workspace. Authentication,
the administrator database-name allowlist/target verification, and the empty seeder
are preserved. No production migrations, administrator bootstrap, Git operations or
existing-database writes were performed. No dependencies or external assets were added.

## Schema and models

The migration `2026_10_30_000100_create_railway_foundation.php` creates eleven tables.
`App\\Support\\RailwaySchema` supplies explicit CREATE TABLE statements so the
same CHECK definitions are exercised by SQLite and MariaDB. It only returns SQL;
the migration executes it when separately authorized.

| Table / model | Relationships and constraints |
| --- | --- |
| `stations` / `Station` | Unique uppercase 1–5 letter railway code; name and city; has route stops. |
| `trains` / `Train` | Unique five-digit number retained as text, including leading zeroes; active flag; has stops, coaches, seats and trips. |
| `train_route_stops` / `TrainRouteStop` | Belongs to train/station; unique position and station per train; increasing route order; arrival/departure offsets. |
| `coaches` / `Coach` | Belongs to train; unique coach label per train; travel class; has seats. |
| `seats` / `Seat` | Belongs to coach/train; unique seat number per coach; validated berth type; composite FK keeps coach and train consistent. |
| `train_trips` / `TrainTrip` | Belongs to train; one journey per origin date; scheduled/cancelled status; has inventory, bookings and waitlist. |
| `trip_seat_inventory` / `TripSeatInventory` | Unique trip/seat pair; available/blocked status; composite FKs ensure trip and seat are from the same train; has assignments. |
| `bookings` / `Booking` | Belongs to user/trip/train; unique reference; boarding and alighting positions must exist on that train in the correct order; nonnegative fare in paise. |
| `passengers` / `Passenger` | Belongs to booking/trip; composite FK enforces the booking's trip; name, age, gender and cancellation state. |
| `seat_assignments` / `SeatAssignment` | Belongs to passenger/inventory/trip; composite FKs prevent cross-trip assignment; at most one active assignment per inventory and per passenger. |
| `waitlist_entries` / `WaitlistEntry` | Belongs to passenger/trip; positive unique position per trip/class; one waiting entry per passenger; waiting/promoted/cancelled history. |

Foreign keys use RESTRICT rather than cascading deletion. Cancellation timestamps
are required exactly when status is cancelled. Waitlist promotion timestamps are
required exactly when status is promoted. Booking/passenger statuses are pending,
confirmed, waitlisted or cancelled. Authenticated booking and cancellation endpoints
are described in the booking guide; the original constraints remain intact.

### Time and route representation

All public journey times use `smartseat.railway_timezone` (Asia/Kolkata). A trip's
`service_date` is the date at the origin. Stops use integer minutes after midnight
on that date: 08:00 on day 0 is 480; 08:00 on day 1 is 1920. Arrival must not exceed
departure, and each stop's arrival must follow the previous stop's departure.
Cross-stop chronological ordering is validated transactionally by the catalogue service.

Route positions may have gaps and range from 1 to 100. A station occurs at most once
on a train's route in this foundation. Offsets range from 0 to 44639 (through day 30).
A date-only model mutator avoids SQLite storing a midnight timestamp in a DATE field.

## Administrator workflow

All catalogue routes use `auth:web` and `can:access-administrator`. Form Requests
authorize administrators, validate input and apply uniqueness rules. Database
constraints remain authoritative if concurrent requests pass validation together.
Scoped route binding prevents editing another train's coaches, stops or seats.

1. Open **Stations** and explicitly add actual station codes, names and cities.
2. Open **Train catalogue** and add a five-digit train number and its name.
3. Open a train's **Route** and configure at least two ordered stops and their times.
4. Open **Coaches**, select a class, then explicitly add seats and berth types.
5. Open **Trips** and choose the origin departure date (today or later in India time).
6. Open the trip's **Inventory** and initialize its explicitly configured seats.

Supported classes are SL, 3A, 2A, 1A, CC, 2S and EC; berth types are lower, middle,
upper, side_lower, side_upper and chair. A train has at most 30 coaches through
the application; seat numbers range from 1 to 200 per coach. Nothing creates coaches
or seats automatically. List pages paginate; route and seat limits bound configuration.

A station cannot be edited after being used by a route. Scheduling freezes all
edits/additions to that train's catalogue, even before a booking exists. This is
deliberately conservative until route/rolling-stock versioning is designed. Future
trips may still be scheduled from the frozen configuration. There are no delete
actions or destructive catalogue operations in this batch.

Catalogue writes, route validation, scheduling and initialization use transactions.
Operations involving a train lock that train first. Inventory initialization also
locks the trip, inserts missing inventory in bounded chunks, and never rewrites
existing status, timestamps or assignments. Repeating initialization is idempotent.
Cancelled trips cannot be initialized. Initialization also invokes FIFO promotion
inside its transaction when waiting groups can use newly available seats.

## Passenger search

The public named route `trains.search` accepts uppercase `origin` and `destination`
station codes plus `journey_date`. The date refers to boarding, not necessarily the
origin date. The request rejects unknown/same stations and invalid/past dates.

Search joins both route stops and requires origin position < destination position.
It excludes inactive trains and cancelled trips. It matches service date plus the
boarding stop's day offset against the requested date, so overnight and multiday
journeys originating earlier are included. A bounded service-date range uses the
date index before the exact boarding-date predicate.

Results paginate 20 trips and show train details, both stations, departure/arrival
in India time and initialized coaches' seat availability. Availability excludes
blocked inventory and active assignments. Aggregation is limited to the current
results page and avoids loading every seat. Uninitialized inventory has an explicit
message, and empty schedules show a clear empty state. Search links into the
authenticated reservation/waitlist flow; no administrator mutation is exposed.

Availability in this foundation is **whole-trip exclusive**, not segment-based:
an active assignment occupies that seat for the trip regardless of boarding and
alighting positions. Results are a snapshot, not a hold or availability guarantee.

## Concurrency contract

The booking engine implements these rules. See its guide for the current locking
protocol and group policy; parallel MariaDB verification remains pending.

- Lock the trip row when modifying waitlist order or promoting entries. Allocate
  monotonically increasing queue positions per trip/class; retain historical positions.
- Reserve by locking the inventory row inside a transaction, checking trip/booking/
  passenger eligibility and inventory status, and inserting an active assignment.
- `active_inventory_id` is generated as inventory_id only for active assignments,
  otherwise NULL. Its UNIQUE index is the database's final protection against double
  allocation. A second generated unique column protects active passenger assignments.
- Cancellation must atomically update booking/passenger state, cancel assignments,
  and cancel/promote waitlist entries as appropriate. Do not delete history.
- Keep a consistent lock order (train, trip, then child rows in stable ID order)
  across reservation, cancellation and promotion; add bounded deadlock retries.
- Cross-table lifecycle rules (for example, no assignment for a cancelled booking,
  and no simultaneous waitlist/seat allocation) must be enforced by that engine's
  locked transactions; CHECK constraints cannot express those cross-row conditions.

SQLite tests verify constraints and transactional rollback. They do not prove
MariaDB row-lock timing, deadlock behavior or concurrent booking-engine correctness.

## Database compatibility and setup

The production DDL targets MariaDB 10.4/InnoDB. It uses explicit foreign keys,
ordinary CHECK constraints, stored generated columns and unique indexes, avoiding
partial indexes or newer MariaDB features. Generated columns are never FK targets;
the underlying ordinary columns carry composite references. See MariaDB's official
[generated-column](https://mariadb.com/docs/server/reference/sql-statements/data-definition/create/generated-columns)
and [constraint](https://mariadb.com/docs/server/reference/sql-statements/data-definition/constraint) references.

SQLite requires 3.31+ for generated columns and foreign-key enforcement enabled;
see [SQLite's generated-column documentation](https://sqlite.org/gencol.html).
MySQL deployments need a version that enforces CHECK constraints (8.0.16+).
Other database drivers and nonempty table prefixes are not supported by this DDL.

No MySQL/MariaDB server was contacted for this batch. MariaDB SQL was compiled and
reviewed, not executed. Before applying it anywhere, separately approve the target,
review migration order and take the usual database backup. The existing users and
role migrations precede the railway migration. No schema or administrator is
installed automatically; do not invoke Composer's broad setup script on an
unreviewed target. The seeder remains empty.

## Safe tests and maintenance

```text
php vendor/phpunit/phpunit/phpunit --do-not-cache-result
php vendor/laravel/pint/builds/pint --test app routes/web.php config/smartseat.php database/factories database/migrations/2026_10_30_000100_create_railway_foundation.php tests
```

The existing test harness replaces all configured connections with SQLite :memory:
before providers boot and checks isolation before any connection use. RailwayTestCase
then applies this railway migration only to that disposable connection. Tests use
DatabaseTransactions and factories; none of those fixtures are an application dataset.
No migration command, seeder, administrator command or real record write is run.

Current results and the complete changed-file inventory are in the
[batch report](railway-batch.md). Changes to constraints must retain both DDL paths
and add regression tests. Live MariaDB compatibility/concurrency and browser visual
QA remain pending; Brave access was denied earlier in this session.

The next verification batch should exercise the implemented engine with genuine
parallel transactions on a separately authorized disposable MariaDB instance.
Fare policy, payments, ticket generation and segment reuse remain separate design
work. Do not begin them automatically.
