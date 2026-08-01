<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridWorkspaceMutationContract;

test('workspace mutations have stable explicit action names', function (): void {
    expect(GridWorkspaceMutationContract::SAVE_COLUMNS)->toBe('save_columns');
    expect(GridWorkspaceMutationContract::RESET_COLUMNS)->toBe('reset_columns');
    expect(GridWorkspaceMutationContract::SAVE_VIEW)->toBe('save_view');
    expect(GridWorkspaceMutationContract::DELETE_VIEW)->toBe('delete_view');
    expect(GridWorkspaceMutationContract::SET_DEFAULT_VIEW)->toBe('set_default_view');
});
