<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkConfirmationPolicy;
use Zoosper\Grid\BulkAction\GridBulkExecutionType;
use Zoosper\Grid\BulkAction\GridBulkSelectionScope;
use Zoosper\Page\Admin\BulkAction\PagePublishSelectedExecutor;

/** Pages contributes declarations only; shared Grid packages own mechanics. */
final class PageGridBulkActions
{
    /** @return list<GridBulkActionDefinition> */
    public static function definitions(): array
    {
        return [
            new GridBulkActionDefinition(
                id: 'export.selected',
                label: 'Export selected',
                selectionScope: GridBulkSelectionScope::EXPLICIT_IDENTITIES,
                executionType: GridBulkExecutionType::CLIENT_DOWNLOAD,
                maximumSelection: 100,
            ),
            new GridBulkActionDefinition(
                id: PagePublishSelectedExecutor::ACTION_ID,
                label: 'Publish selected',
                selectionScope: GridBulkSelectionScope::EXPLICIT_IDENTITIES,
                executionType: GridBulkExecutionType::SERVER_MUTATION,
                confirmationPolicy: GridBulkConfirmationPolicy::CONFIRM,
                requiredPermission: 'page.manage',
                maximumSelection: 100,
                auditRequired: true,
            ),
        ];
    }

    /**
     * Server definitions are intentionally separate from the browser manifest
     * until the protected POST activation phase is deployed.
     *
     * @return list<GridBulkActionDefinition>
     */
    public static function serverDefinitions(): array
    {
        return [
            new GridBulkActionDefinition(
                id: PagePublishSelectedExecutor::ACTION_ID,
                label: 'Publish selected',
                selectionScope: GridBulkSelectionScope::EXPLICIT_IDENTITIES,
                executionType: GridBulkExecutionType::SERVER_MUTATION,
                confirmationPolicy: GridBulkConfirmationPolicy::CONFIRM,
                requiredPermission: 'page.manage',
                maximumSelection: 100,
                auditRequired: true,
            ),
        ];
    }

    private function __construct()
    {
    }
}
