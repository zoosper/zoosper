<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkExecutionType;
use Zoosper\Grid\BulkAction\GridBulkSelectionScope;

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
        ];
    }

    private function __construct()
    {
    }
}
