# Page Grid service registration patch

Merge these services into the current Page service manifest using its existing
container conventions:

```php
use Zoosper\AdminGrid\GridWorkspacePageRenderer;
use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;
use Zoosper\AdminGrid\GridWorkspaceMutationGuard;
use Zoosper\AdminGrid\GridWorkspaceRenderer;
use Zoosper\Grid\GridHtmlRenderer;
use Zoosper\Page\Admin\PageGridAuditedExportCoordinator;
use Zoosper\Page\Admin\PageGridHttpCoordinator;
use Zoosper\Page\Admin\PageGridMutationCoordinator;
use Zoosper\Page\Admin\PageGridMutationHandler;
use Zoosper\Page\Admin\PageGridPageBuilder;
use Zoosper\Page\Admin\PageGridWorkspace;

GridWorkspaceMutationGuard::class => static fn (): GridWorkspaceMutationGuard => new GridWorkspaceMutationGuard(),
GridWorkspaceRenderer::class => static fn (): GridWorkspaceRenderer => new GridWorkspaceRenderer(),
GridWorkspaceMutationFormsRenderer::class => static fn (): GridWorkspaceMutationFormsRenderer => new GridWorkspaceMutationFormsRenderer(),
GridWorkspacePageRenderer::class => static fn (ServiceContainer $services): GridWorkspacePageRenderer => new GridWorkspacePageRenderer(
    mutations: $services->get(GridWorkspaceMutationFormsRenderer::class),
    grid: $services->get(GridHtmlRenderer::class),
),
PageGridWorkspace::class => static fn (ServiceContainer $services): PageGridWorkspace => new PageGridWorkspace(
    definition: $services->get(PageGridDefinition::class),
    resolver: $services->get(GridViewStateResolver::class),
    renderer: $services->get(GridWorkspaceRenderer::class),
),
PageGridMutationHandler::class => static fn (ServiceContainer $services): PageGridMutationHandler => new PageGridMutationHandler(
    definition: $services->get(PageGridDefinition::class),
    mutations: $services->get(GridViewMutationService::class),
),
PageGridHttpCoordinator::class => static fn (ServiceContainer $services): PageGridHttpCoordinator => new PageGridHttpCoordinator(
    workspace: $services->get(PageGridWorkspace::class),
    mutations: $services->get(PageGridMutationHandler::class),
    guard: $services->get(GridWorkspaceMutationGuard::class),
),
PageGridMutationCoordinator::class => static fn (ServiceContainer $services): PageGridMutationCoordinator => new PageGridMutationCoordinator(
    handler: $services->get(PageGridMutationHandler::class),
    guard: $services->get(GridWorkspaceMutationGuard::class),
),
PageGridPageBuilder::class => static fn (ServiceContainer $services): PageGridPageBuilder => new PageGridPageBuilder(
    coordinator: $services->get(PageGridHttpCoordinator::class),
    dataSource: $services->get(PageGridDataSource::class),
    renderer: $services->get(GridWorkspacePageRenderer::class),
),
PageGridAuditedExportCoordinator::class => static fn (ServiceContainer $services): PageGridAuditedExportCoordinator => new PageGridAuditedExportCoordinator(
    $services->get(GridWorkspaceAuditedCsvExportService::class),
),
```
