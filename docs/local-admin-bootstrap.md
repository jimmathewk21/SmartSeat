# Local administrator bootstrap

`smartseat:bootstrap-local-admin` is an explicit, local-only command for provisioning a separate administrator account when a development database has no administrator. It does not run migrations or event/demo seeders and is not registered with `DatabaseSeeder`.

The command refuses to proceed unless `APP_ENV` is `local`, Laravel's active connection uses the MySQL driver and is configured for `smartseat_dev`, and a read-only `SELECT DATABASE()` confirms that the connected database is exactly `smartseat_dev`. It then checks the existing role and permission records before writing. Missing, duplicate, inactive, or unexpected role/permission state causes it to stop without attempting repairs.

The bootstrap uses the existing Admin role ID 1 and requires exactly these permission mappings for that role: `participant_access`, `participant_show`, and `participant_approve`. It uses the Participants workflow because its existing approval action also updates the linked user's verification status. It does not grant the broader `user_edit` permission, which also permits editing user roles. It creates only a new account, prompts for a password twice without echoing it, and stores Laravel's password hash. Existing account passwords and role assignments are never changed. An exact rerun is a no-op. The command never creates or approves a Participant.

## Manual invocation

After confirming the local environment is configured for `smartseat_dev`, run:

```bash
php artisan smartseat:bootstrap-local-admin
```

Enter a new administrator name and email, then provide a password of at least 12 characters at the hidden prompts. Do not pass credentials as command-line arguments.

After provisioning, sign in at `/login` and open `/admin/participants?status=pending`. The Participants page is not currently linked in the admin sidebar, so use this existing route directly. Open the participant and use the existing approval form; it updates both `participants.is_approved` and the linked `users.verification_status`. The bootstrap does not modify any pending participant or change the production approval flow.

Do not use the general `php artisan db:seed` command as a substitute: `DatabaseSeeder` also invokes event-management demo seeders.

## Tests

The bootstrap feature tests require the existing schema in `smartseat_train_test`. Before running them, verify that the configured and active MySQL database is exactly `smartseat_train_test`. Tests use a transaction and roll back their fixtures; they must never run against `smartseat_dev`.
