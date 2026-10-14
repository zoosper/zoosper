# Production acceptance record

Use this record for one exact staging or production deployment. It is evidence, not a substitute for the [production operator runbook](production-operator-runbook.md). Copy the template outside the repository for each deployment and retain only secret-free output. Never record credentials, tokens, reset URLs, TOTP or recovery codes, session identifiers, SMTP passwords, private Media paths, or customer data.

## Deployment identity

- Environment name:
- Acceptance date and UTC time:
- Operator:
- Exact Git commit or verified artifact SHA-256:
- Release/version reported by `php8.5 bin/zoosper version`:
- Canonical origin, without credentials, query, or fragment:
- Selected cache backend: `file` / `redis`
- Database engine and version:
- Rollback code reference recorded: `yes` / `no`
- Database backup recorded: `yes` / `no`
- Uploaded-Media rollback point recorded: `yes` / `no`

A record is accepted only when every applicable check is `PASS`, every non-applicable check has an explicit deployment-specific reason, and no secret-bearing evidence is attached.

## Automated and configuration checks

Record the command, exit status, and a bounded secret-free result. Do not paste environment values.

- [ ] `PASS` Locked production Composer install completed from the committed lock file.
- [ ] `PASS` `composer validate --strict`.
- [ ] `PASS` `composer check-platform-reqs --no-dev`.
- [ ] `PASS` `composer audit --locked --no-interaction`.
- [ ] `PASS` `php8.5 bin/zoosper security:generate-secrets --check` without disclosed values.
- [ ] `PASS` `php8.5 bin/zoosper module:manifest:check`.
- [ ] `PASS` `php8.5 bin/zoosper schema:foreign-keys:status --format=json`; require `add=0`, `mismatch=0`, and `sqlite_rebuild_required=0`.
- [ ] `PASS` `php8.5 bin/zoosper media:queue:status --format=json`; record only aggregate queue telemetry.
- [ ] `PASS` `php8.5 bin/zoosper release:check`.
- [ ] `PASS` Application and exception logs inspected without copying secrets into this record.

## Public origin, proxy, session, and webroot

- [ ] `PASS` Canonical HTTPS redirect and `APP_URL` behaviour verified.
- [ ] `PASS` Direct requests cannot use untrusted forwarded headers to spoof HTTPS or client identity.
- [ ] `PASS` Requests through configured trusted proxies resolve HTTPS and client identity correctly.
- [ ] `PASS` Session cookie is Secure and uses the intended SameSite policy.
- [ ] `PASS` HSTS appears on HTTPS and is absent from plain HTTP responses before redirect.
- [ ] `PASS` Web-server document root is `public/`.
- [ ] `PASS` Repository root, `.env`, Git metadata, Composer metadata, `app/`, `packages/`, `config/`, `storage/`, and `var/` are not publicly retrievable.
- [ ] `PASS` Original Media remains outside `public/`; only controlled validated copies are publicly served.

## Cache and SMTP

- [ ] `PASS` Selected cache backend works in the target environment.
- [ ] `PASS` If Redis is selected, authentication, dedicated database/prefix, network restriction, and a dedicated strong cache encryption key were verified without recording values.
- [ ] `N/A` If file cache is selected, Redis checks are explicitly not applicable.
- [ ] `PASS` Redacted SMTP configuration has no warnings.
- [ ] `PASS` SMTP endpoint is reachable from the application host.
- [ ] `PASS` Controlled password-reset delivery reached the test administrator.
- [ ] `PASS` Delivered reset link uses the exact canonical `APP_URL` and configured Admin base path.
- [ ] `PASS` Reset credential was single-use and was not copied into logs or this record.

## Enforcing CSP browser journey

Confirm the response uses `Content-Security-Policy`, not report-only mode. Record browser/version and a secret-free pass/fail summary. Any blocked required script, style, image, font, or connection is a failure.

- [ ] `PASS` Login, logout, TOTP, and recovery-code authentication.
- [ ] `PASS` Password-reset request and completion.
- [ ] `PASS` Dashboard and personalisation.
- [ ] `PASS` Settings, Users, Roles, and permission-sensitive actions.
- [ ] `PASS` Pages, revisions, preview, publication, and lifecycle actions.
- [ ] `PASS` Menus and frontend navigation.
- [ ] `PASS` Media library, upload, picker, delivery, and lifecycle actions.
- [ ] `PASS` Admin Grid filters, columns, saved views, pagination, exports, and destructive confirmations.
- [ ] `PASS` Browser console contains no deployment-blocking CSP violations.

## Database, workers, and scheduled maintenance

- [ ] `PASS` Target MySQL/MariaDB migration completed against a recorded backup and was not blindly retried after failure.
- [ ] `PASS` Post-migration foreign-key status is fully reconciled.
- [ ] `PASS` Queue worker is supervised and alerts on a non-zero batch exit.
- [ ] `PASS` Queue depth, retries, failures, oldest-pending age, queue wait, and processing latency are within deployment-owned thresholds.
- [ ] `PASS` `rate-limit:prune` is scheduled with overlap protection.
- [ ] `PASS` Scheduler and queue-worker ownership, restart policy, logs, and alert destination are recorded in the deployment platform.

## Post-deployment smoke

- [ ] `PASS` API health and authenticated API access.
- [ ] `PASS` Home, About, Page rendering, Menus, and Media delivery.
- [ ] `PASS` Admin login/logout and authenticated Admin smoke journey.
- [ ] `PASS` TOTP, recovery code, and password-reset delivery/completion.
- [ ] `PASS` Manifest, foreign-key, Media queue, and release checks remain green after deployment.
- [ ] `PASS` Previous code, database backup, and Media rollback point remain available until acceptance.

## Exceptions and decision

For each exception, record the check, observed fact, owner, disposition, and linked secret-free evidence. Do not mark an unverified item as passed.

- Exceptions: none / listed below
- Acceptance decision: `ACCEPTED` / `REJECTED`
- Decision owner:
- Decision UTC time:
- Follow-up work:

C4.2 can be marked complete only after this record is filled from the target environment and independently reviewed. Repository-local tests and static contracts do not replace target-environment evidence.
