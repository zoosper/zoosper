# Phase 3E Grid link-state registration

Merge these additive registrations into the current Page service manifest:

```php
use Zoosper\AdminGrid\GridWorkspaceQuery;
use Zoosper\Page\Admin\PageGridLinks;

GridWorkspaceQuery::class => static fn (): GridWorkspaceQuery => new GridWorkspaceQuery(),
PageGridLinks::class => static fn (ServiceContainer $services): PageGridLinks => new PageGridLinks(
    $services->get(GridWorkspaceQuery::class),
),
```

Use `PageGridLinks` from the latest Grid renderer/controller integration for
pagination, sort and export URLs. Do not reconstruct query strings separately in
templates.
