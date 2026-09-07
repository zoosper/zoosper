# zoosper/auth

Zoosper_Auth module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Auth\` maps to `src/`.

## Architecture

- `src/Access/`
- `src/Acl/`
- `src/Admin/`
- `src/Console/`
- `src/Entity/`
- `src/Http/`
- `src/Layout/`
- `src/Model/`
- `src/RateLimit/`
- `src/Repository/`
- `src/Security/`
- `src/Service/`
- `src/UI/`

## Configuration

- `config/acl.php`: ACL groups and permissions.
- `config/admin_assets.php`: Admin asset contributions.
- `config/admin_menu.php`: Admin navigation items.
- `config/admin_dashboard.php`: the permission-gated active Admin user Dashboard contribution.
- `admin_role_dashboard_preferences`: Auth-owned role defaults with cascading role cleanup; exposed through the dependency-safe `zoosper/admin-dashboard` repository contract.
- `config/admin_middleware.php`: Module runtime configuration.
- `config/admin_routes.php`: Authenticated Admin routes.
- `config/admin_ui.php`: Module runtime configuration.
- `config/console.php`: Console command discovery.
- `config/controllers.php`: Controller factories.
- `config/db_schema.php`: Declarative database schema.
- `config/logging.php`: Module log channel/file.
- `config/services.php`: Service-container bindings.
- `config/services_auth_grid.php`: Module runtime configuration.

## Routes

- `GET /admin/users` from `config/admin_routes.php`.
- `GET /admin/users/create` from `config/admin_routes.php`.
- `POST /admin/users/create` from `config/admin_routes.php`.
- `GET /admin/users/edit` from `config/admin_routes.php`.
- `POST /admin/users/edit` from `config/admin_routes.php`.
- `GET /admin/roles` from `config/admin_routes.php`.
- `GET /admin/roles/create` from `config/admin_routes.php`.
- `GET /admin/access-tokens` from `config/admin_routes.php`.
- `POST /admin/access-tokens/create` from `config/admin_routes.php`.
- `POST /admin/access-tokens/{id}/revoke` from `config/admin_routes.php`.
- `POST /admin/roles/create` from `config/admin_routes.php`.
- `GET /admin/roles/edit` from `config/admin_routes.php`.
- `POST /admin/roles/edit` from `config/admin_routes.php`.

## Dependencies

- `php`: `^8.5`.
- `zoosper/admin-grid`: `^0.3.1@alpha`.
- `zoosper/core`: `^0.3.1@alpha`.
- `zoosper/grid`: `^0.3.1@alpha`.

## Database

- Declarative schema is owned by `config/db_schema.php`.
- Module migrations: `database/migrations/202607090001_create_auth_tables.php`, `database/migrations/202607090002_seed_auth_defaults.php`, `database/migrations/202607090005_seed_user_role_permissions.php`, `database/migrations/202607090007_acl_tree_metadata.php`.

## Extension points

- `config/acl.php` for ACL declarations.
- `config/admin_assets.php` for Admin assets.
- `config/admin_menu.php` for Admin navigation.
- `config/admin_dashboard.php` for the `user.manage`-gated active-user metric through `zoosper/admin-dashboard`; Auth retains ownership of the repository query and does not depend on concrete Admin classes.
- `DashboardRolePreferenceRepository` owns role-default persistence and assigned-role lookup. It implements the package contract so Admin consumes no concrete Auth repository. Role deletion cascades preference cleanup; defaults contain presentation codes only and cannot grant permissions.
- `config/console.php` for console commands.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-auth/tests`.
- Current regression files discovered: `35`. Use `find app/zoosper-auth/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

### Referential integrity
- Admin user-role and role-permission join rows cascade when their owning User, Role, or Permission is removed.
- Declarative schema now mirrors the migration-owned join-table relationships without changing public repository contracts.

### Identity lifecycle and password security

- Admin Users use reversible `active` and `inactive` states. The current account and the last active `super_admin` are protected from disablement.
- Admin User records are retained so audit/login history and ownership references remain attributable.
- Custom Roles may be permanently deleted only when unassigned; `super_admin` remains protected.
- Identity lifecycle mutations are POST-only, centrally CSRF-protected, and permission-gated with `user.manage` or `role.manage`.
- Newly supplied passwords use the configured canonical Admin password policy across HTTP and `admin:create`. The already-present successful-login `password_needs_rehash()` flow was verified and remains covered without forcing a reset.

### Personal Access Tokens
Auth owns hash-only PAT persistence, issuance, revocation, scope validation and active-owner authentication. Plaintext credentials use the `zp_pat_` format and are returned only by the issuance service.

### Admin PAT lifecycle
Authenticated Admin identities can create, list and revoke only their own Personal Access Tokens. Plaintext is rendered directly once after issuance and is never placed in flash messages, redirects or audit metadata.

The Auth-owned PAT screen consumes the shared Admin shell and Admin Grid while keeping its grouped scope picker, one-time copy enhancement and responsive light/dark presentation in CSP-safe Auth assets. The server-rendered form remains usable without JavaScript; enhancement code never submits requests or builds HTML strings. Delete scopes are explicitly identified as destructive. The redesign does not add prototype-only site restrictions, statistics, exports, presets or fake persistence.

### Page lifecycle PAT scopes
- `pages:archive` authorises Page archive and archived-to-draft restoration when the current owner still has `page.manage`.
- `pages:delete` authorises guarded permanent deletion when the current owner still has `page.manage`.

## Admin collection Grid

The module-owned Admin collection index uses a stable Admin Grid workspace with server-side count and page queries, allow-listed sorting, filters, saved views, column visibility, ordering and page-size state. Existing POST lifecycle workflows remain feature-owned.

## Pagination ownership

This package directly consumes the stable `Zoosper\Pagination` request/result boundary through `zoosper/pagination` (`^0.3.1@alpha`). It must not import `Marko\Pagination` classes.

## Admin collection primary actions

Auth Grid create actions remain validated as Admin-local and escaped by `AuthGridPagePresenter`. They use the shared `.admin-page-actions` composition and Admin `.button` component so Users and Roles present a visible, consistently aligned primary action without changing route, permission, or mutation ownership.


## Admin form presentation

User lifecycle actions are edit-only: the create form deliberately omits responder output until an `AdminUser` target exists, while edit operations continue to require the authenticated actor. The Auth-owned Permission Explorer remains progressively enhanced and no-JS compatible, uses Admin semantic theme tokens, and stacks its native controls at responsive breakpoints without changing permission submission, CSRF or mutation behaviour.


### Permission Explorer theme integration

Auth-owned selectors deliberately outrank shared Admin button defaults so Permission Explorer bulk and group controls retain semantic light and dark surfaces. Native buttons, keyboard operation, permission fields, CSRF handling and POST mutation behaviour are unchanged.


### Fable security workspaces

Auth owns the responsive Admin-user identity workspace, PAT creation/review surface, and Permission Explorer assets. Source and public copies remain byte-identical. Assets are screen-scoped and content-versioned; the interfaces remain progressively enhanced, CSP-safe, theme-aware, and usable at narrow widths. User and PAT mutations remain POST-only and CSRF-protected, role assignment remains separately permission-gated, PAT rows remain owner-scoped, plaintext tokens remain one-time-only, and the Permission Explorer runtime performs no network or form submission.

### Admin password reset
Auth owns reset credential persistence and lifecycle. Only a SHA-256 hash of the `zp_reset_` credential is stored; plaintext exists only for immediate delivery. Credentials are issued only for active accounts, expire after the configured service lifetime, are single-use, and a newly issued credential supersedes every outstanding credential for that Admin user.

Reset completion validates the canonical Admin password policy, updates the canonical password hash atomically with credential consumption, invalidates remaining outstanding credentials, and causes existing password-fingerprint sessions to fail on their next guard check. Invalid, expired, consumed, malformed, inactive-account, weak-password, and confirmation-mismatch cases do not change the password.

Public request throttling uses the independent `admin.password_reset_request` policy configured by `RATE_LIMIT_ADMIN_PASSWORD_RESET_MAX_ATTEMPTS` and `RATE_LIMIT_ADMIN_PASSWORD_RESET_WINDOW_SECONDS`. The identity combines normalised email and client IP behind the configured salted rate-limit boundary. Report-only mode observes without blocking; enforce mode denies issuance while the HTTP adapter preserves its neutral public response.

## Admin account lockout

Auth owns temporary per-account protection for repeated failed Admin password authentication. Configuration is supplied through:

```dotenv
ADMIN_ACCOUNT_LOCKOUT_MAX_ATTEMPTS=5
ADMIN_ACCOUNT_LOCKOUT_SECONDS=900
```

`ADMIN_ACCOUNT_LOCKOUT_MAX_ATTEMPTS` is the number of failed passwords for a known active Admin account that causes a lock. `ADMIN_ACCOUNT_LOCKOUT_SECONDS` is the temporary lock duration in seconds. Keep both values positive; the shipped example locks after five failures for 900 seconds.

The lockout record is separate from the Admin user's active/inactive status. Unknown and inactive accounts still use the existing dummy-password verification path and do not receive account-lockout rows. Public login responses remain the neutral `Invalid email or password.` response and do not disclose whether an account exists, is locked, or when a lock expires.

A correct password is still verified while a lock is active, but authentication remains rejected until the lock expires or an authorised operator clears it. A successful login below the threshold clears prior failure state. A completed Admin password reset also clears lockout state inside the reset transaction; rejected, expired, consumed, superseded, mismatched, or weak reset attempts do not clear it.

Account lockout complements the separate Admin request rate limiter. Lockout is keyed to a known active Admin account and persists authentication failure state. Rate limiting protects request traffic using its configured email/IP identity and may return HTTP 429 independently.

The protected Admin User edit workspace displays failed-attempt information and, for an active lock, the expiry in UTC. The POST-only `/admin/users/{id}/unlock` action requires `user.manage` and the standard Admin CSRF middleware. Unlocking clears only lockout state. It does not change status, password, roles, locale, two-factor enrolment, personal access tokens, or password-reset credentials.

A successful operational unlock records `admin_user.account_unlocked` against the target `admin_user` ID with a short secret-free summary. Passwords, hashes, reset tokens, lock expiry, IP addresses, user agents, and session identifiers are excluded from this audit event.
