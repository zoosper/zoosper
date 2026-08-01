# Phase 3K view-action service registration

Merge these additive registrations into the current Admin Grid service manifest:

```php
use Zoosper\AdminGrid\GridWorkspaceViewActionsDecorator;
use Zoosper\AdminGrid\GridWorkspaceViewActionsRenderer;
use Zoosper\AdminGrid\GridWorkspaceViewActionsResolver;

GridWorkspaceViewActionsResolver::class => static fn (ServiceContainer $services): GridWorkspaceViewActionsResolver => new GridWorkspaceViewActionsResolver(
    bookmarks: $services->get(GridWorkspaceActiveBookmark::class),
    dirty: $services->get(GridWorkspaceDirtyState::class),
),
GridWorkspaceViewActionsRenderer::class => static fn (): GridWorkspaceViewActionsRenderer => new GridWorkspaceViewActionsRenderer(),
GridWorkspaceViewActionsDecorator::class => static fn (ServiceContainer $services): GridWorkspaceViewActionsDecorator => new GridWorkspaceViewActionsDecorator(
    actions: $services->get(GridWorkspaceViewActionsResolver::class),
    renderer: $services->get(GridWorkspaceViewActionsRenderer::class),
),
```

Apply the view-actions decorator after `GridWorkspaceStatusDecorator`. The HTTP
forms remain the CSRF-bearing forms from `GridWorkspaceMutationFormsRenderer`;
the host integration should map clicked `data-grid-view-action` controls onto the
corresponding protected action field rather than adding unprotected endpoints.
