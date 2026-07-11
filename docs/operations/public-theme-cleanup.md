# Public theme cleanup

Run the migration first:

```bash
php tools/migrate-public-theme-assets.php
```

Verify what remains:

```bash
php tools/audit-public-theme-assets.php
```

Remove legacy public theme files:

```bash
php tools/remove-public-themes-directory.php --yes
```

Then verify:

```bash
php tools/verify-admin-asset-consolidation.php
php tools/verify-project-structure.php
php tools/audit-public-webroot.php
```
