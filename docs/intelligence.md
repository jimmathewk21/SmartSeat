# SmartSeat intelligence

The implementation is original PHP code with no external ML dependency or imported
dataset. No genuine historical data was read or trained on during implementation.
No production model, fabricated history or sample railway dataset is shipped.
Ordinary tests use isolated SQLite fixtures and arithmetic unit fixtures only;
test model artifacts use fake storage and are removed after each pipeline test.

## Availability and target

With no accepted trained model, passengers see exactly:

> Prediction unavailable — insufficient historical data.

No heuristic percentage or default 50% replaces missing evidence. Predictions are
optional; booking, cancellation and queue behavior do not require a model artifact.

The binary target is whether the whole waitlisted group receives a confirmed
allocation before its actual boarding departure. A later voluntary cancellation
does not erase the fact that it was promoted. Estimates are for groups, not individual
passengers, and are not guarantees, confidence intervals or promises of a ticket.

The estimate uses the group's immutable state at joining, evaluated by the current
accepted model. The UI states this time basis. It does not pretend to be a continuously
updated prediction for each new queue position.

## Historical feature extraction and leakage controls

An additive migration creates waitlist_prediction_snapshots and preference metadata.
After an authenticated group joins the waitlist, BookingEngine captures one snapshot
within its existing transaction. Snapshots cannot be updated through the model and
have no editing endpoint. Capturing can be disabled using
smartseat.intelligence.capture_waitlist_history; missing snapshot tables skip capture.
No model training happens on booking, startup, or ordinary page rendering.

Features, in a fixed versioned order:

- Log of hours to boarding departure, waiting passengers ahead, configured class
  capacity, occupied inventory, blocked inventory and prior-history sample count.
- Group size, count of explicitly requested accessible seats, count of explicit
  lower/side-lower preferences, and the keep-group-together flag.
- Previous promotion and waiting-cancellation rates for the same train/class.
- Boarding weekday sine/cosine and one-hot coach class.

Historical rates use at most 500 earlier mature snapshots and only audit events
visible at capture time. A zero history count marks no evidence; zero numerical
rate values in that case are missing-input encodings, never displayed probabilities.
Names, gender, diagnoses and contact information are not model inputs. Train/route
context determines cutoff, cohort and capacity; raw train IDs are not one-hot
features that could memorize rare services.

Training labels use real booking audit events no later than departure and the
training extraction cutoff. A group is positive when an audit transition goes from
waitlisted to confirmed before departure. Both positive and negative examples must
be mature: departure plus 24 hours must have passed. Current final booking status
is not substituted for this event-based target.

Pre-module bookings without snapshots are not backfilled from today's occupancy or
cancellation rates. Such reconstruction would leak future information. Capture
genuine new history instead, or design and review an independently provable as-of
import in a separate batch. No such import is provided here.

## Model and evaluation

The learner is L2-regularized binary logistic regression, independently implemented
with full-batch gradient descent in PHP. This is a small interpretable statistical
model suited to limited tabular data and probability output. It avoids an unnecessary
Python service or large dependency. Choosing it does not imply accuracy before
real evaluation.

Implementation: training-only mean/standard-deviation scaling; unregularized intercept;
L2 coefficient 0.02; learning rate 0.1; at most 800 epochs; early stop at gradient
norm below 0.001. Publication requires gradient norm below 0.01. Constant features
have a protected scale. The stable sigmoid and finite/shape validation prevent
invalid numerical outputs. Hyperparameters are fixed, not tuned on validation labels.

The newest at most 10,000 compatible mature snapshots are ordered chronologically.
The final 20% by observation time is validation. Earlier rows whose outcomes were
not mature at the validation boundary are purged. Thus all fitted labels were
knowable when held-out predictions would have occurred. Scaling is fit on training
only. The accepted coefficients are not subsequently refitted on the held-out rows.

Default publication requirements:

- At least 200 training groups and 100 validation groups.
- At least 20 outcomes of each label in both partitions.
- Lower held-out Brier score and log loss than the training-rate constant baseline.
- Five-bin calibration evaluation on at least 100 held-out groups, with expected
  calibration error at most 0.10.

These are conservative initial engineering gates, not a statistical certification.
The admin view shows sample counts and reliability-bin counts/means/outcomes.
Small bins remain uncertain. Calibration uses validation only for evaluation, not
for fitting an additional calibrator. Thresholds may be changed only with review.

Inference abstains for missing, corrupt, incompatible or older-than-90-day models,
missing snapshots, departed groups and features outside observed training ranges.
Single-feature range checks do not prove joint-distribution coverage. Unseen route
effects, drift and rare accessibility cohorts remain limitations. Extreme estimates
display as <1% or >99% rather than rounded certainty.

Conceptual references, not implementation dependencies:
[logistic regression](https://scikit-learn.org/stable/modules/linear_model.html#logistic-regression),
[probability calibration](https://scikit-learn.org/stable/modules/calibration.html),
and [data leakage](https://scikit-learn.org/stable/common_pitfalls.html#data-leakage).

## Training, persistence and retraining

Administrators open Prediction models from the control room/sidebar. GET shows
availability, chronological split counts, active model/version/training time and
held-out metrics. It does not train.

POST training requires the administrator Gate, CSRF, recent password confirmation,
an explicit checkbox, rate limiting and a cache lock. Training reads genuine mature
snapshots only. Insufficient data or failed evaluation leaves the accepted model
unchanged. No training request against an existing database was executed by the agent.

Accepted JSON artifacts live privately under:
storage/app/private/intelligence/waitlist/models/<version>.json
with an active.json pointer. They include algorithm/feature versions, scaling,
weights, training bounds, timestamps, split counts, metrics and a dataset fingerprint.
No raw training rows or personal identities are exported. Version files cannot be
overwritten; same-directory atomic replacement updates the checksum-protected pointer.
Only strict supported JSON is loaded; no executable PHP serialization is used.

To retrain later, first obtain authorization to use genuine historical data, review
retention/cutoffs and data availability, then explicitly use the admin form. Do not
manufacture observations merely to meet minimum counts. Retain prior accepted
versions for comparison and backup under the application's private-storage policy.
Apply the additive migration only on a separately authorized target.

## Personalized deterministic recommendations

This feature is a rule-based ranking/assignment service, separate from ML.

Passengers can request a berth type, verified accessible seating and soft group
proximity. No medical details are collected. Age 60+ adds a soft lower-berth
convenience preference only when no explicit berth preference exists; it never
creates an accessibility requirement or diagnosis.

Seat accessibility defaults to unknown. Administrators explicitly record unknown,
accessible or not_accessible after physical verification. A lower berth is not
automatically accessible. Configure this before scheduling, because the existing
catalogue freeze remains intact. Software cannot verify platform/coach suitability.

Ranking and group matching:

- An explicit accessible-seat requirement is a hard filter; unknown is insufficient.
- Accessibility-needing passengers are allocated first. Flexible passengers avoid
  consuming verified accessible seats when other preferences tie.
- Explicit berth matches score above age-based convenience and group proximity.
- When proximity is requested, the service evaluates coach anchors, favors the
  same coach and nearby seat numbers, then deterministically breaks ties by ID.
- It returns one distinct seat per passenger or no whole-group plan. This is a
  bounded greedy heuristic, not an optimal physical-adjacency guarantee.

Every suggestion has explanations. The page never preselects or replaces manual
choices. Use these suggestions for review is an explicit consent action leading
to review, not a reservation. All availability is rechecked in the locked booking
transaction. A manual seat choice may override a soft berth preference; a conflicting
hard accessibility requirement is rejected with instructions to choose a verified
seat or explicitly update that requirement.

Required accessibility is also honored during automatic FIFO promotion. Groups
remain all-or-nothing; an eligible later group cannot bypass a head group whose
required seats are currently unavailable. Requests exceeding configured verified
accessible capacity are rejected rather than waiting forever. Whole-trip seat
exclusivity and original unique/FK/CHECK protections remain unchanged.

Preferences are persisted with passengers/bookings. Default-preference submission
fingerprints remain compatible with the previous booking engine's idempotency keys.

## MariaDB concurrency preparation

Prepared files in tests/MariaDb are not discovered by the default Unit/Feature suites.
They have not been executed against MariaDB. Syntax, the pure target guard and
Windows process/transport coordination now have database-free tests. Database
behavior still requires future approval and live verification; see
[worker coordination](worker-coordination.md).

- ApprovedTarget validates one exact name:
  smartseat_rebuild_test, local host, integer port, the restricted
  smartseat_test_runner username, password and an exact explicit approval phrase.
- Runtime validates before Laravel/PDO use, then verifies actual database identity
  and MariaDB 10.4. Only a pre-provisioned complete schema is accepted. The parent
  refuses any existing application rows. A separate guarded migrate.php now
  prepares only an already-created target with zero tables after approval.
  No database creation or DB cleanup code is supplied.
- run.php creates minimal fixtures only after that approval/identity/empty-target
  preflight. It never uses the application's default existing connection or writes
  .env/config files. ML history capture is disabled for disposable test fixtures.
- ParallelWorkers starts independent PHP processes with database credentials through
  nonce/PID-verified loopback sockets, never arguments/files. Workers must show
  distinct connection IDs and reach an actual transaction-begin socket barrier
  before proceeding. Timing overlap, successful exits and bounded deadlines are checked.
- Cases cover same-seat claims, duplicate submissions, concurrent cancellation plus
  multiple promoters, injected allocation rollback, injected engine retry,
  uncoordinated database uniqueness and a genuine opposing-lock deadlock/retry probe.
- Fault cases assert the intended fault actually occurred; an unexercised fault is
  a failure, not a passing concurrency claim. Reports contain no credentials.
- Temporary worker-output files stay under project storage/framework/testing. Only
  known files from that run are cleaned after workers stop. Database fixtures remain, requiring separately approved
  cleanup and a fresh empty target before another run.

After explicit target/process/fixture approval and schema provisioning, the future
entry point is php tests/MariaDb/run.php with its descriptor supplied securely via
stdin. Do not put credentials in command arguments or tracked files. Descriptor
keys are approval, database, host, port, username and password. The approval value
must exactly be: I approve fresh migrations and disposable concurrency tests on smartseat_rebuild_test.

See [reviewable commands and approval scope](mariadb-approval.md) before any run.

This is preparation, not permission to execute. See the [existing test plan](mariadb-booking-test-plan.md).
Do not claim concurrency is fully verified until genuine parallel scenarios have
been run successfully on the approved MariaDB target and their traces reviewed.

## Tests, limits and next work

The ordinary PHPUnit suite uses the existing fail-closed SQLite memory harness.
New migrations are applied only there. Learner arithmetic/persistence tests use
explicit isolated unit fixtures on fake storage, never a real historical database.
They prove implementation mechanics, not actual prediction accuracy. No production
model artifact or synthetic historical dataset is delivered.

See [batch results and file inventory](intelligence-batch.md). Remaining work is
genuine historical-data accumulation/evaluation, privacy/retention review, explicit
MariaDB test authorization/execution and desktop/mobile visual QA. Browser access
was denied earlier in this session; automated Blade rendering is covered.

Do not automatically implement payments, deploy a model or run concurrency tests.
