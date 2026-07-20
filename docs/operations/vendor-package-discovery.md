# Vendor package discovery operations

Phase 1.37q.1 fixes the vendor discovery fixture test so both fixture cases include Composer metadata forms used by real installs:

```text
vendor/composer/installed.json
vendor/composer/installed.php
```

Run targeted tests:

```bash
vendor/bin/pest app/zoosper-core/tests/Unit/Module/VendorPackageDiscoveryReadinessTest.php app/zoosper-core/tests/Unit/Composer/VendorPackageDiscoveryAuditToolTest.php
```

Run audit:

```bash
php8.5 tools/audit-vendor-package-discovery.php
```

Run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```
