<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridWorkspaceMutationMessages;
use Zoosper\AdminGrid\GridWorkspaceMutationResult;
use Zoosper\AdminGrid\GridWorkspaceMutationContract;

test('mutation result accepts only application-local redirect paths', function (): void {
    $result = new GridWorkspaceMutationResult(
        GridWorkspaceMutationContract::SAVE_VIEW,
        GridWorkspaceMutationMessages::forAction(GridWorkspaceMutationContract::SAVE_VIEW),
        '/admin/pages',
    );

    expect($result->redirectPath)->toBe('/admin/pages');
    expect(fn () => new GridWorkspaceMutationResult('save_view', 'Saved', 'https://example.invalid'))
        ->toThrow(\InvalidArgumentException::class, 'absolute application path');
});

test('every stable mutation action has a success message', function (): void {
    foreach ([
        GridWorkspaceMutationContract::SAVE_COLUMNS,
        GridWorkspaceMutationContract::RESET_COLUMNS,
        GridWorkspaceMutationContract::SAVE_VIEW,
        GridWorkspaceMutationContract::DELETE_VIEW,
        GridWorkspaceMutationContract::SET_DEFAULT_VIEW,
    ] as $action) {
        expect(GridWorkspaceMutationMessages::forAction($action))->not->toBe('');
    }
});











