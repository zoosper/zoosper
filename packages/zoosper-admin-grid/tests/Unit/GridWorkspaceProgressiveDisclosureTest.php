<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

it('passes the real bookmark collection into the compact toolbar', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/GridCompactWorkspaceRenderer.php',
    );

    expect($source)->not->toBeFalse()
        ->and($source)->toContain('$state->bookmarks,')
        ->and($source)->toContain('$state->activeBookmarkId,')
        ->and($source)->toContain('$formAction,');
});

it('keeps Grid settings hidden until requested from the command bar', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/GridWorkspaceMutationFormsRenderer.php',
    );

    expect($source)->not->toBeFalse()
        ->and($source)->toContain(
            '<details id="grid-workspace-settings" class="grid-workspace__settings" data-grid-settings hidden>',
        )
        ->and($source)->toContain('<summary><strong>Grid settings</strong>')
        ->and($source)->not->toContain('data-grid-settings open');
});

it('publishes progressive disclosure styling', function (): void {
    $root = dirname(__DIR__, 4);
    $assets = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';

    expect($assets['assets'])->toHaveKey('zoosper-admin-grid-progressive-disclosure-style');
});











