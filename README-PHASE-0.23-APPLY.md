# Apply Zoosper Phase 0.23 Admin Grid Pagination, Search and Filters

Apply from repository root:

```bash
unzip zoosper-phase-0.23-admin-grid-pagination-search-filters-update.zip -d /tmp/zoosper-phase-0.23
cp -R /tmp/zoosper-phase-0.23/zoosper-phase-0.23-admin-grid-pagination-search-filters-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l app/zoosper-core/src/Pagination/Pager.php
php -l app/zoosper-core/src/Pagination/PaginationResult.php
php -l app/zoosper-page/src/Admin/PageGridCriteria.php
php -l app/zoosper-page/src/Admin/PageGridRepository.php
php -l app/zoosper-page/resources/views/admin/pages/index.php
php -l themes/admin/default/templates/components/grid/page-filters.php
php -l themes/admin/default/templates/components/grid/pagination.php
```

Controller integration:

```text
app/zoosper-page/config/controllers.php.phase023_patch.md
app/zoosper-page/src/Controller/PageAdminController.phase023_index_patch.md
```

Browser test after controller integration:

```text
/admin/pages
/admin/pages?q=home
/admin/pages?status=published
/admin/pages?page=2&page_size=20
```

Roadmap included:

```text
Phase 0.24 - Admin 2FA Foundation
Phase 0.25 - Admin Form Field Injection Implementation
```
