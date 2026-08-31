<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridWorkspaceMutationContract;
use Zoosper\AdminGrid\GridWorkspaceMutationGuard;
use Zoosper\AdminGrid\GridWorkspaceRequest;

test('workspace mutations require POST', function (): void {
    expect(fn (): string => (new GridWorkspaceMutationGuard())->assertAllowed(
        new GridWorkspaceRequest('GET', ['action' => 'save_view']),
    ))->toThrow(\InvalidArgumentException::class, 'require POST');
});

test('only explicit workspace actions are accepted', function (): void {
    $guard = new GridWorkspaceMutationGuard();

    expect($guard->assertAllowed(new GridWorkspaceRequest('POST', post: [
        'action' => GridWorkspaceMutationContract::SAVE_VIEW,
    ])))->toBe('save_view');

    expect(fn (): string => $guard->assertAllowed(new GridWorkspaceRequest(
        'POST',
        post: ['action' => 'delete_everything'],
    )))->toThrow(\InvalidArgumentException::class, 'Unsupported');
});











