# Phase 3D Page export repository binding

Merge this additive binding into the current Page service manifest:

```php
use Zoosper\Page\Admin\PageGridExportRepositoryInterface;
use Zoosper\Page\Admin\PageGridExportSqlBuilder;
use Zoosper\Page\Admin\PdoPageGridExportRepository;

PageGridExportRepositoryInterface::class => static fn (ServiceContainer $services): PageGridExportRepositoryInterface => new PdoPageGridExportRepository(
    pdo: $services->get(PDO::class),
    sql: $services->get(PageGridExportSqlBuilder::class),
),
```

Before merging, compare the `SELECT` projection and table names in
`PdoPageGridExportRepository` with the current Page schema. If the current
repository already owns an equivalent canonical SELECT, move that projection
behind `PageGridExportRepositoryInterface` instead of maintaining two copies.
