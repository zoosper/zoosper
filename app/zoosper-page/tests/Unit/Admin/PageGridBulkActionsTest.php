<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Grid\BulkAction\GridBulkExecutionType;
use Zoosper\Grid\BulkAction\GridBulkSelectionScope;
use Zoosper\Page\Admin\PageGridBulkActions;

it('declares only the supported Pages export-selected action', function (): void {
    $actions = PageGridBulkActions::definitions();
    expect($actions)->toHaveCount(1)
        ->and($actions[0]->id)->toBe('export.selected')
        ->and($actions[0]->selectionScope)->toBe(GridBulkSelectionScope::EXPLICIT_IDENTITIES)
        ->and($actions[0]->executionType)->toBe(GridBulkExecutionType::CLIENT_DOWNLOAD);
});
