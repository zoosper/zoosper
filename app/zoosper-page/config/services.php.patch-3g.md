# Phase 3G complete Page Grid presentation registration

Merge these additive registrations into the current Page service manifest:

```php
use Zoosper\AdminGrid\GridWorkspaceCompletePageRenderer;
use Zoosper\Page\Admin\PageGridCompletePageBuilder;

GridWorkspaceCompletePageRenderer::class => static fn (ServiceContainer $services): GridWorkspaceCompletePageRenderer => new GridWorkspaceCompletePageRenderer(
    $services->get(GridWorkspaceNavigationRenderer::class),
),
PageGridCompletePageBuilder::class => static fn (ServiceContainer $services): PageGridCompletePageBuilder => new PageGridCompletePageBuilder(
    page: $services->get(PageGridPageBuilder::class),
    navigation: $services->get(PageGridNavigationBuilder::class),
    renderer: $services->get(GridWorkspaceCompletePageRenderer::class),
),
```

The live Page controller should obtain current/total page metadata from the
existing PaginationResult and pass it through `GridWorkspacePagination`. It should
render the returned `GridWorkspaceCompletePage::html()` through the existing admin
layout/view boundary.
