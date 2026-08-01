# Phase 3I view-status service registration

Merge these additive registrations into the current Admin Grid service manifest:

```php
use Zoosper\AdminGrid\GridWorkspaceActiveBookmark;
use Zoosper\AdminGrid\GridWorkspaceViewStatusRenderer;
use Zoosper\AdminGrid\GridWorkspaceViewStatusResolver;

GridWorkspaceActiveBookmark::class => static fn (): GridWorkspaceActiveBookmark => new GridWorkspaceActiveBookmark(),
GridWorkspaceViewStatusRenderer::class => static fn (): GridWorkspaceViewStatusRenderer => new GridWorkspaceViewStatusRenderer(),
GridWorkspaceViewStatusResolver::class => static fn (ServiceContainer $services): GridWorkspaceViewStatusResolver => new GridWorkspaceViewStatusResolver(
    bookmarks: $services->get(GridWorkspaceActiveBookmark::class),
    dirty: $services->get(GridWorkspaceDirtyState::class),
),
```

Resolve status from the same `GridViewState` used for rows and prepend the rendered
status inside the workspace toolbar. Do not accept a browser-provided dirty flag.
