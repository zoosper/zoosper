# Apply Zoosper Phase 0.4 update files

This package contains only the files changed or added for Phase 0.4.

From your repository root:

```bash
unzip zoosper-phase-0.4-update-files.zip -d /tmp/zoosper-phase-0.4
cp -R /tmp/zoosper-phase-0.4/zoosper-phase-0.4-update-files/* .
chmod +x tools/fix-migration-pdo-warnings.sh
./tools/fix-migration-pdo-warnings.sh
composer dump-autoload
php bin/zoosper migrate
```

Then test:

```text
/admin/pages
/admin/pages/create
/admin/pages/edit?id=1
/admin/pages/preview?id=1
```

Commit suggestion:

```bash
git add .
git commit -m "Add admin page CRUD"
```
