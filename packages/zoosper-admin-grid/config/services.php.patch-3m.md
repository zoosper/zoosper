# Phase 3M page-size service registration

Merge these additive registrations into the current Admin Grid service manifest:

```php
use Zoosper\AdminGrid\GridWorkspacePageSizeDecorator;
use Zoosper\AdminGrid\GridWorkspacePageSizeOptions;
use Zoosper\AdminGrid\GridWorkspacePageSizeRenderer;

GridWorkspacePageSizeOptions::class => static fn (): GridWorkspacePageSizeOptions => new GridWorkspacePageSizeOptions([20, 50, 100, 200]),
GridWorkspacePageSizeRenderer::class => static fn (ServiceContainer $services): GridWorkspacePageSizeRenderer => new GridWorkspacePageSizeRenderer(
    $services->get(GridWorkspacePageSizeOptions::class),
),
GridWorkspacePageSizeDecorator::class => static fn (ServiceContainer $services): GridWorkspacePageSizeDecorator => new GridWorkspacePageSizeDecorator(
    $services->get(GridWorkspacePageSizeRenderer::class),
),
```

Apply the page-size decorator to the workspace GET form before status and view-
action decorators. Ensure `GridStateNormaliser` uses the same allow-list rather
than accepting arbitrary browser values.
