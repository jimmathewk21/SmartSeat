# Windows worker coordination

The opt-in MariaDB runner no longer uses anonymous stdin/stdout/stderr pipes for
coordination. On Windows those pipes cannot supply the nonblocking behavior the
old polling loop assumed. Waiting for pipe output could prevent the parent from
releasing the very barrier awaited by its children.

## Transport and process lifecycle

- `WorkerCoordinator` is database-free and binds a nonblocking TCP listener only
  on `127.0.0.1`, using an OS-assigned ephemeral port. It directly launches 2–4 PHP
  children with an argument array and no shell. Each run has fresh child nonces.
- `WorkerChannel` handles JSON-line framing, partial reads/writes, EOF and malformed
  input on nonblocking sockets. Frames/buffers are limited to 64 KiB; each poll
  does bounded work. Failed socket setup or I/O aborts the group.
- The parent checks each child's reported PID against `proc_get_status`, rejects
  duplicate identities and verifies the nonce before sending a request. Database
  credentials travel only in the loopback request, never arguments or files.
  The endpoint, slot and short-lived coordination nonce appear in arguments;
  this is process correlation, not protection against a debugger running as the
  same OS user. Use a trusted local test environment.
- Ready workers wait for `go`. In MariaDB mode the parent additionally requires
  distinct positive `CONNECTION_ID()` values before releasing them. After actual
  Laravel `TransactionBeginning` events, all workers must report `begun` before
  the parent sends `begin`. The deadlock probe similarly synchronizes both first
  row locks with `locked`/`lock`. Retry attempts do not repeat the first-attempt
  barriers. Final timestamps must still demonstrate overlapping transactions.
- The overall monotonic deadline defaults to 60 seconds (maximum 120). Child
  connection establishment is capped at five seconds and all later communication
  shares a child deadline. Results must be followed by process exit within one
  second. Crashes, disconnects, unexpected phases/output and nonzero exits fail
  the group. Complete results and successful process exits are both required.
- On failure the parent closes sockets, terminates children and polls termination
  with a shared two-second shutdown deadline, escalating termination if needed.
  It does not call blocking `proc_close` on a child still reported running. Failure
  to observe termination is an error, not success.
- stdin is an empty workspace file. Unexpected stdout/stderr is captured only in
  private run-specific workspace files, triggers failure, and is never copied into
  error messages. Exception arguments are suppressed in children. After all children
  stop, only the known files for that run are removed. No recursive cleanup or
  database cleanup is performed. If OS termination fails, files are retained.

`ParallelWorkers::run` still validates `ApprovedTarget` before spawning. The real
worker still invokes `Runtime::boot`, whose exact target, local connection,
MariaDB version, engine and constraint checks remain unchanged. Booking code,
transaction retries, fault injection and database scenario assertions are unchanged.
The generic coordinator has no PDO/SQL or Laravel bootstrap and cannot weaken the
production entry point's approval guard.

## Database-free verification

```text
php vendor/phpunit/phpunit/phpunit --do-not-cache-result tests/Unit/WorkerCoordinationTest.php
php vendor/phpunit/phpunit/phpunit --do-not-cache-result
```

The first command uses `tests/Fixtures/coordination-worker.php`. That fixture loads
only WorkerChannel: no Laravel, Composer application bootstrap, database driver
calls, SQL, migrations or existing credentials. It exercises the same parent
coordinator/transport used by the real worker. It checks separate PIDs, two/four
workers, staggered readiness, begin/lock synchronization, messages larger than a
single write, repeated runs, early exits, readiness/begin stalls, delayed exits,
sanitized stderr, invalid phases, fragmented/malformed/oversized/truncated frames,
cleanup and duplicate connection-ID protocol rejection. The latter uses dummy
metadata only, never actual database connections. The production target guard is
also checked before any spawn.

Passing these tests establishes local Windows process/transport readiness. The
fixture's simulated work intervals are not database transactions, and dummy IDs
are not evidence of independent MariaDB sessions. Actual migrations, row locks,
deadlocks and booking races remain unverified until separately approved execution
of the [disposable MariaDB procedure](mariadb-approval.md).

The runner is ready for that approval-controlled test attempt once server/account/
grant preflight succeeds. This does not grant approval or establish MariaDB
compatibility. Local firewall/software must allow loopback IPC and `proc_open`.

## Verified results and changed files

On Windows with PHP 8.2.12:

- Dedicated database-free coordination suite: **15 tests, 65 assertions passed**.
- Complete PHPUnit suite: **234 tests, 1,897 assertions passed**, retaining all
  219 previously passing tests.
- PHP syntax checks: **141 source files passed**. Laravel Pint across the project passed.
- Updated documentation links passed; no worker run directories/files remained.
- MariaDB was never connected to or modified; its migrations and real concurrency
  scenarios were not run. Existing integration tests used isolated SQLite memory
  databases only. `.env`, APP_KEY and Git state were not modified.

Created: tests/MariaDb/WorkerChannel.php, tests/MariaDb/WorkerCoordinator.php,
tests/Fixtures/coordination-worker.php, tests/Unit/WorkerCoordinationTest.php and
this guide. Modified: tests/MariaDb/ParallelWorkers.php, tests/MariaDb/worker.php,
docs/mariadb-approval.md, docs/mariadb-booking-test-plan.md, docs/intelligence.md
and docs/release-readiness.md. Application booking code and target/security
verification code were not changed.
