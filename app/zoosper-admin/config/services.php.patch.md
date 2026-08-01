# Admin service registration patch

Merge this additive binding into the current `app/zoosper-admin/config/services.php`:

```php
use Zoosper\Admin\Grid\AdminGridAuditLoggerBridge;
use Zoosper\AdminGrid\GridWorkspaceAuditLoggerInterface;
use Zoosper\Core\Audit\AuditLoggerInterface;

GridWorkspaceAuditLoggerInterface::class => static fn (ServiceContainer $services): GridWorkspaceAuditLoggerInterface => new AdminGridAuditLoggerBridge(
    $services->get(AuditLoggerInterface::class),
),
```

Admin owns this bridge. The Admin Grid package must not import the Admin namespace.
