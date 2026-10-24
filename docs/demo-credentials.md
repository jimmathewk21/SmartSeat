# Development-only synthetic credential reference

Step 27A creates implementation and documentation only. **No database accounts, passwords or credential reference have been installed.** Unit tests use temporary in-memory business-data arrays. No administrator credentials are requested, copied, replaced, or included in this feature.

After an explicitly approved Step 27B installation, the installer exclusively creates:

```text
storage/app/private/smartseat-demo/S27-v1/credentials.json
```

This is outside `public/` and `storage/app/public/`. The existing `storage/app/.gitignore` excludes it. It must never be linked from public/passenger pages, attached to a commit, exposed through a route, or uploaded with public assets. Unix file permissions are requested as `0600` and directory permissions as `0700`; on Windows, verify the directory's ACL before installation because PHP `chmod` does not establish a Windows user-only ACL.

The file is the separate local credential reference for these fictional accounts:

| Kind | Address pattern | Count | Initial access |
| --- | --- | ---: | --- |
| Passenger | `s27.passenger.001@example.invalid` through `s27.passenger.100@example.invalid` | 100 | Dedicated train sign-in, train access enabled, event approval disabled. |
| Train Owner | `s27.owner.001@example.invalid` through `s27.owner.005@example.invalid` | 5 | Pending administrator approval; private entry path is recorded in the private reference. |

Every account has an independently generated 192-bit random password, hashed through Laravel `Hash::make` before database insertion. Passwords and hash salts are intentionally **not deterministic** and are not derived from the dataset seed. Only business data is repeatable. Plaintext passwords are written exclusively to the private reference and never printed by the command. No default/shared password or production-facing credential example is supplied.

Query-builder insertion avoids user-model observers, registration mail, verification mail and notifications to synthetic addresses. `.invalid` addresses are deliberately non-deliverable and are never real people's credentials.

An administrator must review each synthetic owner through the existing Admin → Users workflow and explicitly approve Train Owner access. Role membership, the existing owner-role permissions, approval and assigned trains remain necessary. Passengers do not receive the Train Owner or Admin role; owners are not automatically event-approved.

Reruns do not overwrite the credential reference, change existing passwords, or repair an interrupted installation. If installation fails after creating the file, retain it and review transaction/marker state before any retry. If the file is missing after a successful installation, use a separately authorized reset for the synthetic accounts; the generator cannot reconstruct random passwords. Do not reset the existing administrator.

See [dataset design, exact counts, safety gates and later commands](railway-demonstration-dataset.md). The installer and preview must not be invoked during Step 27A.
