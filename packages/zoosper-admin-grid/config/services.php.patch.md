# Admin Grid service registration patch

Merge the following registrations into the current
`packages/zoosper-admin-grid/config/services.php` rather than replacing the file
from an older phase snapshot.

```php
use Zoosper\AdminGrid\GridWorkspaceAuditedCsvExportService;
use Zoosper\AdminGrid\GridWorkspaceCsvExportService;
use Zoosper\AdminGrid\GridWorkspaceExportAuditorFactory;
use Zoosper\AdminGrid\GridWorkspaceExportAuditorInterface;
use Zoosper\AdminGrid\GridWorkspaceExportPolicy;
use Zoosper\AdminGrid\GridWorkspaceAuditLoggerInterface;
use Zoosper\AdminGrid\NullGridWorkspaceExportAuditor;
use Zoosper\Grid\GridCsvExporter;

GridWorkspaceExportPolicy::class => static fn (): GridWorkspaceExportPolicy => new GridWorkspaceExportPolicy(),
GridCsvExporter::class => static fn (): GridCsvExporter => new GridCsvExporter(),
GridWorkspaceCsvExportService::class => static fn (ServiceContainer $services): GridWorkspaceCsvExportService => new GridWorkspaceCsvExportService(
    exporter: $services->get(GridCsvExporter::class),
    policy: $services->get(GridWorkspaceExportPolicy::class),
),
GridWorkspaceExportAuditorInterface::class => static fn (ServiceContainer $services): GridWorkspaceExportAuditorInterface => GridWorkspaceExportAuditorFactory::create(
    $services->has(GridWorkspaceAuditLoggerInterface::class)
        ? $services->get(GridWorkspaceAuditLoggerInterface::class)
        : null,
),
GridWorkspaceAuditedCsvExportService::class => static fn (ServiceContainer $services): GridWorkspaceAuditedCsvExportService => new GridWorkspaceAuditedCsvExportService(
    exports: $services->get(GridWorkspaceCsvExportService::class),
    auditor: $services->get(GridWorkspaceExportAuditorInterface::class),
),
```

The factory provides `NullGridWorkspaceExportAuditor` when the host bridge is
absent, preserving optional Admin/Audit installation.
