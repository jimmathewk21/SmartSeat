# Development-only synthetic credential reference

Step 27C hardens implementation without installing data. **No database accounts or application credential reference have been installed.** Offline tests use in-memory arrays and isolated temporary directories which are cleaned afterward. No administrator credentials are requested, copied, replaced, or included in this feature.

Only during a separately authorized installation, after target/schema/role/namespace checks, the installer exclusively creates:

```text
storage/app/private/smartseat-demo/S27-v1/credentials.json
```

This is outside `public/` and `storage/app/public/`. The existing `storage/app/.gitignore` excludes it. It must never be linked from public/passenger pages, attached to a commit, exposed through a route, or uploaded with public assets. A restrictive creation umask, exclusive file creation and file locking are used, with Unix file permissions `0600` and new directory permissions `0700`. Existing directories are not chmodded. On Windows, verify the private directory's ACL before installation because PHP `chmod` does not establish a Windows user-only ACL. Use a trusted local directory; hostile filesystem replacement cannot be made atomic with a SQL commit.

The file is the separate local credential reference for these fictional accounts:

| Kind | Address pattern | Count | Initial access |
| --- | --- | ---: | --- |
| Passenger | `s27.passenger.001@example.invalid` through `s27.passenger.100@example.invalid` | 100 | Dedicated train sign-in, train access enabled, event approval disabled. |
| Train Owner | `s27.owner.001@example.invalid` through `s27.owner.005@example.invalid` | 5 | Pending administrator approval; private entry path is recorded in the private reference. |

Every account has an independently generated 192-bit random password, hashed through Laravel `Hash::make` before database insertion. Passwords and hash salts are intentionally **not deterministic** and are not derived from the dataset seed. Only business data is repeatable. Plaintext passwords are written exclusively to the private reference and never printed by the command. No default/shared password or production-facing credential example is supplied.

Query-builder insertion avoids user-model observers, registration mail, verification mail and notifications to synthetic addresses. `.invalid` addresses are deliberately non-deliverable and are never real people's credentials.

An administrator must review each synthetic owner through the existing Admin → Users workflow and explicitly approve Train Owner access. Role membership, the existing owner-role permissions, approval and assigned trains remain necessary. Passengers do not receive the Train Owner or Admin role; owners are not automatically event-approved.

On a caught installation failure, the reference owned by that attempt is truncated/flushed, then removed only if its filesystem identity still matches. A pre-existing or replaced path is never deleted. Short writes, failed flushes, permission errors and cleanup failures cannot report success; errors omit password/hash input. The reference is retained only after the transaction callback has committed. Cleanup failure reports an explicit manual-review error, even if the remaining artifact is already empty.

Reruns do not overwrite references, change existing passwords, or repair interrupted installations. A forced kill/power failure can bypass cleanup, and a lost commit acknowledgement can leave committed accounts without a usable reference. Review transaction/marker state before any retry. If the file is missing after a successful installation, use a separately authorized reset for the synthetic accounts; the generator cannot reconstruct random passwords. Do not reset the existing administrator.

See [dataset design, exact counts, safety gates and later commands](railway-demonstration-dataset.md). Preview is offline; dataset installation has not been authorized or executed.
