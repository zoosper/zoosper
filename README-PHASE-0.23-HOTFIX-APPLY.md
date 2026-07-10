# Apply Phase 0.23 Admin Pages Grid Hotfix

Apply from repository root:

```bash
unzip zoosper-phase-0.23-admin-pages-grid-hotfix-roadmap.zip -d /tmp/zoosper-phase-0.23-hotfix
cp -R /tmp/zoosper-phase-0.23-hotfix/zoosper-phase-0.23-admin-pages-grid-hotfix-roadmap/* .
composer dump-autoload
```

Smoke test:

```bash
php -l themes/admin/default/templates/partials/components/grid/page-filters.php
php -l themes/admin/default/templates/partials/components/grid/pagination.php
```

Browser test:

```text
/admin/pages
/admin/pages?q=home
/admin/pages?status=published
/admin/pages?page=2&page_size=20
```

If escaped toolbar HTML still appears, inspect `themes/admin/default/templates/layout.php` and ensure the admin content slot renders raw trusted HTML:

```php
<main class="admin-content"><?= $content ?></main>
```
