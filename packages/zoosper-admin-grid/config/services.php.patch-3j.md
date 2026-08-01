# Phase 3J decorated workspace service registration

Merge these additive registrations into the current Admin Grid service manifest:

```php
use Zoosper\AdminGrid\GridWorkspaceDecoratedRenderer;
use Zoosper\AdminGrid\GridWorkspaceStatusDecorator;

GridWorkspaceStatusDecorator::class => static fn (ServiceContainer $services): GridWorkspaceStatusDecorator => new GridWorkspaceStatusDecorator(
    status: $services->get(GridWorkspaceViewStatusResolver::class),
    renderer: $services->get(GridWorkspaceViewStatusRenderer::class),
),
GridWorkspaceDecoratedRenderer::class => static fn (ServiceContainer $services): GridWorkspaceDecoratedRenderer => new GridWorkspaceDecoratedRenderer(
    workspace: $services->get(GridWorkspaceRenderer::class),
    status: $services->get(GridWorkspaceStatusDecorator::class),
),
```

Update the Page `PageGridWorkspace` service factory to request
`GridWorkspaceDecoratedRenderer::class` instead of `GridWorkspaceRenderer::class`.
