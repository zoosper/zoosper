# Phase 3B Page service additions

Merge these registrations into the current Page service manifest after Phase 3A:

```php
use Zoosper\Page\Admin\PageGridControllerAdapter;
use Zoosper\Page\Admin\PageGridExportDataSourceInterface;
use Zoosper\Page\Admin\PageGridExportRequestCoordinator;

PageGridExportRequestCoordinator::class => static fn (ServiceContainer $services): PageGridExportRequestCoordinator => new PageGridExportRequestCoordinator(
    workspace: $services->get(PageGridHttpCoordinator::class),
    rows: $services->get(PageGridExportDataSourceInterface::class),
    exports: $services->get(PageGridAuditedExportCoordinator::class),
),
PageGridControllerAdapter::class => static fn (ServiceContainer $services): PageGridControllerAdapter => new PageGridControllerAdapter(
    pages: $services->get(PageGridPageBuilder::class),
    mutations: $services->get(PageGridMutationCoordinator::class),
    exports: $services->get(PageGridExportRequestCoordinator::class),
),
```

Bind `PageGridExportDataSourceInterface` to the current Page repository adapter
that can stream all rows matching `GridCriteria` up to the export policy ceiling.
The adapter must use bound Site-ID parameters and must not apply the screen pager.
