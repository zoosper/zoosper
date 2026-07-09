# Apply Zoosper Phase 0.9 Audit/Login History Update

Apply from repository root:

```bash
unzip zoosper-phase-0.9-audit-login-history-update.zip -d /tmp/zoosper-phase-0.9
cp -R /tmp/zoosper-phase-0.9/zoosper-phase-0.9-audit-login-history-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l database/migrations/202607090006_create_audit_login_history.php
php -l app/zoosper-admin/src/Audit/AuditLogger.php
php -l app/zoosper-admin/src/Audit/AuditLogRepository.php
php -l app/zoosper-admin/src/Audit/LoginHistoryRepository.php
php -l app/zoosper-admin/src/Controller/AuditLogController.php
php -l app/zoosper-admin/src/Controller/LoginHistoryController.php
php -l app/zoosper-admin/src/Controller/LoginController.php
php -l app/zoosper-core/src/Http/Request.php
```

Browser test:

```text
/admin/login-history
/admin/audit-log
```

Note: two patch-helper files are included for role user assignment:

```text
app/zoosper-auth/src/Repository/AdminUserRepository.admin_audit_patch.md
app/zoosper-auth/src/Repository/RoleRepository.additions.php
```

Apply those snippets into repositories before enabling assign-users-from-role UI.
