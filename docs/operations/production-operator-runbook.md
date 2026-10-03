# Production operator runbook

This runbook is the canonical pre-deployment and post-deployment checklist for a Zoosper staging or production installation. Run commands from the project root with PHP 8.5. Never paste secrets into logs, tickets, shell history, or public issue trackers.

## 1. Obtain and verify the release

Deploy a clean tracked checkout or a verified production artifact. Install the committed lock file and verify the real production platform:

```bash
php8.5 "$(command -v composer)" install --no-dev --no-interaction --prefer-dist --optimize-autoloader --classmap-authoritative
php8.5 "$(command -v composer)" validate --strict
php8.5 "$(command -v composer)" check-platform-reqs --no-dev
php8.5 "$(command -v composer)" audit --locked --no-interaction
```

The web-server document root must be the project `public/` directory. Do not expose the repository root, `app/`, `packages/`, `config/`, `storage/`, `var/`, `.env`, Composer metadata, or Git files. Uploaded originals remain outside `public/`; only validated Media copies belong under `public/media/`.

## 2. Configure the public origin and reverse proxies

Set `APP_URL` to the canonical absolute HTTPS origin. It must not contain credentials, query data, or a fragment. Password-reset links are built from this configured origin and must never be derived from an untrusted Host header.

Set `TRUSTED_PROXIES` only when Zoosper is behind known reverse proxies or load balancers. Use a comma-separated list of the immediate proxy IPv4/IPv6 addresses or CIDR ranges. Do not trust broad networks merely to make forwarded headers work. Invalid entries fail boot. Verify that direct requests cannot spoof `X-Forwarded-Proto` or the client address, and that HTTPS requests behind a trusted proxy produce Secure session cookies and HSTS.

Required public-environment controls include:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cms.example.com
SESSION_SECURE=true
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MODE=enforce
DATABASE_ENFORCE_MYSQL_PRODUCTION=true
DB_CONNECTION=mysql
```

Process-manager or container environment values take precedence over `.env`.

## 3. Generate and audit secrets

Create `.env` from the shipped example, restrict it to the deployment account, and use the repository-owned command:

```bash
cp .env.example .env
chmod 600 .env
php8.5 bin/zoosper security:generate-secrets --write
php8.5 bin/zoosper security:generate-secrets --check
```

The command manages `APP_KEY`, `TWO_FACTOR_ENCRYPTION_KEY`, `RATE_LIMIT_IDENTITY_SALT`, and `CACHE_ENCRYPTION_KEY`, writes through a checked same-directory temporary file, verifies mode `0600`, and does not print secret values in audit mode. Keep each secret single-purpose. Before rotating the 2FA key, place the retired key in `TWO_FACTOR_PREVIOUS_ENCRYPTION_KEYS`; remove old keys only after enrolled administrators have successfully signed in and their secrets have been re-encrypted.

## 4. Configure MySQL or MariaDB

Use a dedicated least-privilege application account and the deployment-owned `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. Record a database backup and uploaded-Media rollback point before changing code or schema.

Inspect and apply in this order:

```bash
php8.5 bin/zoosper migrate
php8.5 bin/zoosper schema:foreign-keys:status --format=json
php8.5 bin/zoosper schema:foreign-keys:apply --confirm=apply
php8.5 bin/zoosper schema:foreign-keys:status --format=json
```

Require `add=0`, `mismatch=0`, and `sqlite_rebuild_required=0` after reconciliation. Review every proposed statement before applying it. MySQL DDL can partially succeed, so inspect status after any failure instead of blindly retrying. The ordinary `migrate` command has no dry-run mode. Rehearse release upgrades against a disposable database using the repository-owned release-upgrade tools before touching production.

## 5. Configure cache safely

The file cache is the default and does not require Redis credentials. When selecting Redis:

```dotenv
CACHE_DRIVER=redis
CACHE_REDIS_HOST=127.0.0.1
CACHE_REDIS_PORT=6379
CACHE_REDIS_PASSWORD=<deployment-secret>
CACHE_REDIS_DATABASE=0
CACHE_REDIS_PREFIX=zoosper:cache:
CACHE_ENCRYPTION_KEY=<dedicated-strong-secret>
```

Staging and production reject unauthenticated Redis and weak or placeholder cache signing keys. The current Redis boundary supports password authentication and does not expose a separate ACL username. Restrict network access to the application hosts, use a dedicated logical database and prefix, and never reuse the Redis password as the cache signing key.

## 6. Configure SMTP and password-reset links

Configure `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `SMTP_HOST`, `SMTP_PORT`, and any required `SMTP_USERNAME`, `SMTP_PASSWORD`, and `SMTP_ENCRYPTION`. Use the configured timeout deliberately. Confirm SMTP reachability from the application host and send a controlled password-reset message to a test administrator. Reset messages bypass Email Logs because their URLs contain single-use credentials; do not copy reset URLs into diagnostics or tickets.

Confirm the delivered reset URL uses the exact `APP_URL` origin and configured Admin base path.

## 7. Validate CSP and the Admin experience

Production uses enforcing CSP:

```dotenv
SECURITY_CSP_ENABLED=true
SECURITY_CSP_REPORT_ONLY=false
SECURITY_CSP_REPORT_URI=
```

If a reporting endpoint is configured, ensure it is trusted and monitored. Validate the emitted response header is `Content-Security-Policy`, not `Content-Security-Policy-Report-Only`. Exercise login, TOTP, password reset, Dashboard, Settings, Users, Roles, Pages, Menus, Media, Grid filters, saved views, exports, and destructive confirmation flows. Treat blocked scripts, styles, images, fonts, or connections as deployment failures. Do not weaken the policy globally; document and test only the narrow source needed by a real feature.

## 8. Compile and run release checks

```bash
php8.5 bin/zoosper compile
php8.5 bin/zoosper module:manifest:check
php8.5 bin/zoosper release:check
```

Require the compiled manifest to be fresh and every release check to pass. Ensure `var/cache`, `var/log`, the application-owned session directory, HTML Purifier cache, private Media storage, and controlled public Media directory have the minimum required ownership and permissions.

## 9. Schedule maintenance and workers

Schedule `php8.5 bin/zoosper rate-limit:prune` with overlap protection. If queued Media processing is enabled, run `php8.5 bin/zoosper media:process-queue` under a supervised worker and monitor failures. See the rate-limit pruning runbook for scheduler examples.

## 10. Security disclosure and dependency monitoring

Monitor the private mailbox in `SECURITY.md` and follow its acknowledgement and coordinated-disclosure process. Do not ask reporters to open public issues or include live credentials, tokens, TOTP codes, recovery codes, or payment data.

Run `composer audit --locked --no-interaction` in CI and before releases. Repository owners must separately enable and monitor dependency alerts, code scanning, secret scanning, and branch protection in the repository hosting settings. Treat new advisories or leaked-secret alerts as operational incidents, rotate affected credentials, and record only secret-free evidence.

## 11. Post-deployment smoke checks

Verify:

- HTTPS redirect and canonical `APP_URL` behavior.
- Secure session cookie attributes and Admin login/logout.
- TOTP and recovery-code authentication.
- Password-reset delivery and single-use completion.
- API health and authenticated API access.
- Home, About, Page rendering, Menus, Media delivery, and Admin Grid workflows.
- Application and exception logs contain no secrets.
- `module:manifest:check`, `schema:foreign-keys:status --format=json`, and `release:check` remain green.

Keep the previous code, database backup, and uploaded-Media rollback point until the deployment is accepted.
