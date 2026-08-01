<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridWorkspaceMutationContract;
use Zoosper\AdminGrid\GridWorkspaceMutationFormIds;

test('protected mutation actions have stable unique form IDs', function (): void {
    $ids = [
        GridWorkspaceMutationFormIds::forAction(GridWorkspaceMutationContract::SAVE_VIEW),
        GridWorkspaceMutationFormIds::forAction(GridWorkspaceMutationContract::SET_DEFAULT_VIEW),
        GridWorkspaceMutationFormIds::forAction(GridWorkspaceMutationContract::DELETE_VIEW),
        GridWorkspaceMutationFormIds::forAction(GridWorkspaceMutationContract::SAVE_COLUMNS),
        GridWorkspaceMutationFormIds::forAction(GridWorkspaceMutationContract::RESET_COLUMNS),
    ];
    expect(array_unique($ids))->toHaveCount(5);
});

test('view action asset only targets fixed forms and existing canonical fields', function (): void {
    $script = (string) file_get_contents(dirname(__DIR__, 2) . '/resources/admin/js/grid-workspace-view-actions.js');
    expect($script)->toContain("save_view: 'grid-workspace-save-view'")
        ->toContain('input[name="view_name"]')
        ->not->toContain('innerHTML')
        ->not->toContain('fetch(')
        ->not->toContain('admin_user_id')
        ->not->toContain('grid_key');
});
