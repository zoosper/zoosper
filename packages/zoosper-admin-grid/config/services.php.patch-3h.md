# Phase 3H dirty-state service registration

Merge these additive registrations into the current Admin Grid service manifest:

```php
use Zoosper\AdminGrid\GridWorkspaceDirtyState;
use Zoosper\AdminGrid\GridWorkspaceStateFingerprint;

GridWorkspaceStateFingerprint::class => static fn (): GridWorkspaceStateFingerprint => new GridWorkspaceStateFingerprint(),
GridWorkspaceDirtyState::class => static fn (ServiceContainer $services): GridWorkspaceDirtyState => new GridWorkspaceDirtyState(
    $services->get(GridWorkspaceStateFingerprint::class),
),
```

The Page presentation may compare the resolved state with the active bookmark's
stored state and render an `Unsaved changes` indicator. Page number changes must
not dirty a saved view. Save View and Save as Default View continue to persist the
complete normalised state through the existing mutation service.
