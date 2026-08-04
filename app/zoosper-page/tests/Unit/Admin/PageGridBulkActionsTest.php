<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Grid\BulkAction\GridBulkConfirmationPolicy;
use Zoosper\Grid\BulkAction\GridBulkExecutionType;
use Zoosper\Grid\BulkAction\GridBulkSelectionScope;
use Zoosper\Page\Admin\PageGridBulkActions;

it('declares the supported Pages export and publish-selected actions', function (): void {
    $actions = PageGridBulkActions::definitions();

    expect($actions)->toHaveCount(2)
        ->and($actions[0]->id)->toBe('export.selected')
        ->and($actions[0]->selectionScope)->toBe(GridBulkSelectionScope::EXPLICIT_IDENTITIES)
        ->and($actions[0]->executionType)->toBe(GridBulkExecutionType::CLIENT_DOWNLOAD)
        ->and($actions[1]->id)->toBe('page.publish')
        ->and($actions[1]->selectionScope)->toBe(GridBulkSelectionScope::EXPLICIT_IDENTITIES)
        ->and($actions[1]->executionType)->toBe(GridBulkExecutionType::SERVER_MUTATION)
        ->and($actions[1]->confirmationPolicy)->toBe(GridBulkConfirmationPolicy::CONFIRM)
        ->and($actions[1]->requiredPermission)->toBe('page.manage')
        ->and($actions[1]->maximumSelection)->toBe(100)
        ->and($actions[1]->auditRequired)->toBeTrue();
});
