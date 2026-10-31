# Disposable MariaDB concurrency test plan

Status: NOT EXECUTED. No database was created, accessed or modified for this plan.
SQLite results do not prove concurrency safety on MariaDB.

The current [exact commands and approval scope](mariadb-approval.md) target the new
isolated `smartseat_rebuild_test` database. Neither preparation nor this document
authorizes execution. Guarded fresh migration code is now prepared separately.

An opt-in implementation is now prepared in tests/MariaDb. See
[harness operation and limits](intelligence.md#mariadb-concurrency-preparation).
It is excluded from the default suite and has not been executed against MariaDB.
The default suite now includes database-free tests of the same Windows-safe
coordinator and framed socket transport; see [worker coordination](worker-coordination.md).
Those tests do not execute the real MariaDB worker or prove database concurrency.

## Required future approval

Obtain explicit approval for an exact disposable local database name, server/port,
credentials limited to it, fixture creation, migrations and independent workers.
Exact name for review: smartseat_rebuild_test.
This proposal is not permission to create/access it.

A future opt-in harness must fail closed on driver, approved exact name and local
host; forbid URLs, sockets and split connections. Never fall back to the application's
default connection, .env or an existing database. Use runtime configuration only;
do not modify .env or APP_KEY. After approval/connection, verify SELECT DATABASE()
matches exactly. Do not use smartseat_dev or run the administrator bootstrap.
Cleanup/drop requires separate destructive-action approval.

## Execution setup

Use MariaDB 10.4/InnoDB with enforced constraints and REPEATABLE READ. Record exact
versions/settings. Apply users/role, foundation and additive booking migrations
only to the approved target. Use minimal test fixtures after approval, never a
general sample railway dataset. Keep this harness opt-in and separate from default
SQLite PHPUnit tests, with an approval token plus exact-target guard.

Use at least two independent PHP processes/connections, synchronization barriers,
begin/lock/commit/rollback timing and a separate final-inspection connection.
Sequential calls or two operations on one connection are not parallel verification.

## Required races

| Scenario | Required outcome |
| --- | --- |
| Two owners select one seat | One active allocation; loser gets conflict feedback; no partial group/events. |
| Overlapping multi-seat groups | Whole-group success or failure; never partial allocation. |
| Same owner/key/payload twice | Same reference, one group and one set of lifecycle events. |
| Same owner/key/different payloads | At most one commit; no overwritten details. |
| Same booking cancelled twice | One cancellation, no release of promoted passengers' seats. |
| Two cancellations on one train | Correct atomic release and oldest eligible group promotion. |
| Promotion overlaps initialization/cancellation | One promotion per group; consistent assignment/passenger/audit state. |
| Concurrent waitlist joins | Unique positions and active entries, creation-time/ID FIFO. |
| Large head group and later small group | No queue bypass, including direct seat reservation. |
| Different boarding stops/times | Departed requests expire; no segment reuse or duplicate trip-seat assignment. |
| Contention under REPEATABLE READ | No stale-snapshot promotion after waiting for parent locks. |
| Forced deadlock/lock timeout | Bounded whole-transaction retry; graceful exhausted-retry result without partial state. |

Inject failures after the first assignment, after release but before promotion,
and during audit writes. Include initialization's nested transaction/deadlock path.
Inspect committed outcomes from a separate connection after workers finish.

After each run verify active assignment uniqueness, matching train/trip FKs,
group-atomic statuses, submission-key uniqueness, no waiting entries for confirmed/
cancelled engine passengers, no assignments for cancelled passengers, and intact
historical rows. Repeat with reversed arrival order and controlled scheduling.

## Report and limits

Record target identity without credentials, worker/connection IDs, barriers, timings,
outcomes, invariant queries and repeat counts. Keep credentials out of command
arguments, logs and tracked files. Report skipped/failing scenarios. Do not claim
concurrency is fully verified if races fail or only serial execution is observed.

No production migration, real booking, payment, notification, machine learning or
recommendation work is authorized by this plan.
