# Phase 3F Grid navigation registration

Merge these additive registrations into the current Page service manifest:

```php
use Zoosper\AdminGrid\GridWorkspaceNavigationRenderer;
use Zoosper\Page\Admin\PageGridNavigationBuilder;

GridWorkspaceNavigationRenderer::class => static fn (): GridWorkspaceNavigationRenderer => new GridWorkspaceNavigationRenderer(),
PageGridNavigationBuilder::class => static fn (ServiceContainer $services): PageGridNavigationBuilder => new PageGridNavigationBuilder(
    $services->get(PageGridLinks::class),
),
```

The latest Page presentation should build navigation from the resolved state and
render the resulting Previous, Next and Export links. Sortable table headers
should use `GridWorkspaceNavigation::sortUrls` rather than rebuilding queries.
