# Phase 3C Page export data-source additions

Merge these registrations into the current Page service manifest:

```php
use Zoosper\Page\Admin\PageGridExportDataSource;
use Zoosper\Page\Admin\PageGridExportDataSourceInterface;
use Zoosper\Page\Admin\PageGridExportRepositoryInterface;
use Zoosper\Page\Admin\PageGridExportSqlBuilder;

PageGridExportSqlBuilder::class => static fn (): PageGridExportSqlBuilder => new PageGridExportSqlBuilder(),
PageGridExportDataSourceInterface::class => static fn (ServiceContainer $services): PageGridExportDataSourceInterface => new PageGridExportDataSource(
    $services->get(PageGridExportRepositoryInterface::class),
),
```

Bind `PageGridExportRepositoryInterface` to the current PDO-backed Page repository
adapter. Its `stream()` method should compose the existing Page SELECT and row
projection with `PageGridExportSqlBuilder`, bind every returned parameter, avoid
LIMIT/OFFSET from the screen pager, and yield rows incrementally. The generic
export policy remains the final hard row ceiling.
