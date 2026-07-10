# Apply Phase 0.25 Admin 2FA Foundation and Auto Schema Fix

Apply from repository root:

```bash
unzip zoosper-phase-0.25-admin-2fa-foundation-auto-schema.zip -d /tmp/zoosper-phase-0.25
cp -R /tmp/zoosper-phase-0.25/zoosper-phase-0.25-admin-2fa-foundation-auto-schema/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l config/two_factor.php
php -l app/zoosper-url-rewrite/config/db_schema.php
php -l app/zoosper-two-factor/config/db_schema.php
php -l app/zoosper-two-factor/src/Service/TotpSecretGenerator.php
php -l app/zoosper-two-factor/src/Service/RecoveryCodeGenerator.php
php -l app/zoosper-two-factor/src/Service/RecoveryCodeHasher.php
php -l app/zoosper-two-factor/src/Service/TwoFactorSecretProtector.php
php -l app/zoosper-two-factor/src/Repository/AdminTwoFactorRepository.php
```

Expected migration result:

```text
url_rewrites
admin_user_two_factor
admin_user_recovery_codes
admin_two_factor_challenges
```

If any table is not created, inspect the existing declarative schema runner because database scripts should be auto-run by `bin/zoosper migrate` without manual SQL.
