<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use PDO;
use Zoosper\AdminGrid\GridCompactWorkspaceRenderer;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\Grid\GridColumnRegistry;
use Zoosper\Grid\GridHtmlRenderer;

/**
 * Builds the complete Auth Grid read-side object graph.
 *
 * This factory owns no HTTP, authentication or write behaviour. Controllers will
 * continue to supply the authenticated administrator identity explicitly.
 */
final readonly class AuthGridPageBuilderFactory
{
    public function __construct(
        private PDO $pdo,
        private GridViewStateResolver $stateResolver,
        private ?GridColumnRegistry $columnRegistry = null,
        private ?GridColumnOrderer $columnOrderer = null,
        private ?GridCompactWorkspaceRenderer $workspaceRenderer = null,
        private ?GridHtmlRenderer $gridRenderer = null,
    ) {
    }

    public function adminUsers(): AdminUserGridPageBuilder
    {
        $definition = new AdminUserGridDefinition($this->columnRegistry);

        return new AdminUserGridPageBuilder(
            workspace: new AdminUserGridWorkspace(
                definition: $definition,
                workspace: $this->workspace(),
            ),
            dataSource: new AdminUserGridDataSource(
                new PdoAdminUserGridReadRepository(
                    $this->pdo,
                    new AdminUserGridSqlBuilder(),
                ),
            ),
            renderer: $this->gridRenderer ?? new GridHtmlRenderer(),
        );
    }

    public function roles(): RoleGridPageBuilder
    {
        $definition = new RoleGridDefinition($this->columnRegistry);

        return new RoleGridPageBuilder(
            workspace: new RoleGridWorkspace(
                definition: $definition,
                workspace: $this->workspace(),
            ),
            dataSource: new RoleGridDataSource(
                new PdoRoleGridReadRepository(
                    $this->pdo,
                    new RoleGridSqlBuilder(),
                ),
            ),
            renderer: $this->gridRenderer ?? new GridHtmlRenderer(),
        );
    }

    private function workspace(): AuthGridWorkspace
    {
        return new AuthGridWorkspace(
            resolver: $this->stateResolver,
            renderer: $this->workspaceRenderer ?? new GridCompactWorkspaceRenderer(),
            columnOrderer: $this->columnOrderer,
        );
    }
}
