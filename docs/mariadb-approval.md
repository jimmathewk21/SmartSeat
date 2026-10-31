# MariaDB commands awaiting approval

Status: **Live migration/concurrency results are unverified.** This is an optional
operator test plan, not part of installing or sharing the source repository.
Each developer supplies their own server and restricted credentials. Never reuse
an existing application database or publish credentials, authentication hashes,
database exports or local account details. See the [source preparation guide](source-repository.md).

Approve a new local database named exactly `smartseat_rebuild_test`, the exact
server/port, a new restricted account, fresh migrations, disposable fixtures and
parallel workers before running the following. Cleanup/drop needs separate approval.
No database cleanup SQL or destructive script is supplied.

The Windows pipe-handling blocker is addressed by nonblocking loopback TCP IPC.
The database-free coordination suite verifies separate processes, start/begin/lock
barriers and bounded failure/shutdown paths; see [worker coordination](worker-coordination.md).
This verifies the runner, not MariaDB compatibility or database race outcomes.

## DBA preparation: after approval only

Resolve the approved MariaDB 10.4 executable. Open its client interactively with
a DBA account; `-p` prompts without placing a password in command arguments:

```text
mariadb --no-defaults --protocol=TCP --host=127.0.0.1 --port=3306 --user=<approved-dba-account> -p --batch --skip-auto-rehash
```

An authorized DBA must first verify the server identity, MariaDB 10.4/InnoDB,
enforced CHECK/foreign-key constraints, and the absence of the proposed test
database/account. An existing target must cause a stop, not silent reuse.

Provision a new empty database named exactly `smartseat_rebuild_test` and a new
`smartseat_test_runner` account restricted to `127.0.0.1`, through the DBA's normal
secure process. Choose a unique private password locally. This guide intentionally
contains no executable account-password template that could be copied literally.
Do not place a password in shell arguments, command history, transcripts or reports.

The account needs SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX and REFERENCES
only on this exact test database. Database-level grant patterns must escape the
underscores (`smartseat\_rebuild\_test`) to prevent wildcard matches. No DROP/global
data privileges, roles or GRANT OPTION are needed. Verify the effective restricted
account, database and complete grants before running either harness entry point.
Keep raw SHOW GRANTS output private if it contains authentication material; share
sanitized privilege metadata only. See [MariaDB GRANT documentation](https://mariadb.com/docs/server/reference/sql-statements/account-management-sql-statements/grant).

## Guarded migration and concurrency commands: after approval only

Run in the project root with development dependencies, PDO MySQL and proc_open.
The password goes through stdin, not command arguments or files. No environment or
connection configuration file is changed. It exists briefly in process memory;
do not enable shell transcription. Use the approved port if different from 3306.

```powershell
$testSecret = Read-Host 'Disposable test database password' -AsSecureString
$testPointer = [IntPtr]::Zero
try {
    $testPointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($testSecret)
    $testTarget = @{
        approval = 'I approve fresh migrations and disposable concurrency tests on smartseat_rebuild_test'
        database = 'smartseat_rebuild_test'
        host = '127.0.0.1'
        port = 3306
        username = 'smartseat_test_runner'
        password = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($testPointer)
    } | ConvertTo-Json -Compress
    $testTarget | php tests/MariaDb/migrate.php
    if ($LASTEXITCODE -ne 0) { throw 'Stop: migrations failed; do not reuse the target.' }
    $testTarget | php tests/MariaDb/run.php
    if ($LASTEXITCODE -ne 0) { throw 'Stop: concurrency suite failed; fixtures may remain.' }
} finally {
    if ($testPointer -ne [IntPtr]::Zero) {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($testPointer)
    }
    Remove-Variable testTarget,testSecret -ErrorAction SilentlyContinue
}
```

`migrate.php` validates the exact target/account/phrase before boot/PDO, replaces
all database connections before providers boot, verifies SELECT DATABASE and
MariaDB 10.4, and refuses any existing tables. It creates the migration repository,
applies all seven migrations in order and checks zero application records.
DDL failure may leave a partial schema; there is no automatic rollback/drop.

`run.php` requires the complete schema, empty application tables, InnoDB,
foreign_key_checks=1, check_constraint_checks=1 and REPEATABLE READ. It creates only
disposable test users and railway fixtures on this exact target. History capture
is disabled; there is no ML training. Distinct worker connections and actual
transaction-begin socket barriers must demonstrate overlap. It checks:

- FK/CHECK rejection, including invalid case and newline station codes.
- Generated-column active-seat uniqueness under uncoordinated competing writes.
- Simultaneous seat claims and duplicate submissions.
- Concurrent cancellation/promotion and oldest-group FIFO ordering.
- Whole-group rollback after a second-seat allocation fault.
- Engine retry/deduplication and a real opposing-lock deadlock with retry.

Fixtures remain; rerunning against their nonempty target is refused. Only known
workspace worker-output files are removed. Windows process/transport coordination
has database-free coverage; all actual MariaDB results still require these commands
to run successfully after approval. No standalone Artisan command inherits the
wrapper's temporary connection configuration; do not substitute ordinary Artisan
migration commands or migrate:fresh.

## Safeguards and reporting

ApprovedTarget accepts only `smartseat_rebuild_test`, `smartseat_test_runner`, the
exact approval phrase, a loopback host and integer port. Extra keys, URLs, sockets
and split connections are refused. Runtime contains no alternative connection.
The old concurrency name and every application database name are rejected. The
administrator bootstrap allowlist is unchanged; its command is never invoked.
No connection to `smartseat_dev` is used for discovery or these tests.

The broader [race matrix](mariadb-booking-test-plan.md#required-races) also describes
future stress/variation cases beyond this prepared suite. Report each live
scenario separately; a passing subset is not proof of full concurrency safety.
