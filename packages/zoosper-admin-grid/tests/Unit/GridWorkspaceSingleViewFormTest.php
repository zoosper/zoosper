<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceMutationContract;
use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;

it('renders one shared view name input with two explicit save actions', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents($root . '/packages/zoosper-admin-grid/src/GridWorkspaceMutationFormsRenderer.php');

    expect($source)->not->toBeFalse()
        ->and(substr_count($source, 'name="view_name"'))->toBe(1)
        ->and($source)->toContain('GridWorkspaceMutationContract::SAVE_VIEW')
        ->and($source)->toContain('GridWorkspaceMutationContract::SET_DEFAULT_VIEW')
        ->and($source)->toContain('Save &amp; make default')
        ->and($source)->not->toContain('bool $asDefault');
});

it('uses clicked submit buttons to carry mutation actions', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents($root . '/packages/zoosper-admin-grid/src/GridWorkspaceMutationFormsRenderer.php');

    expect($source)->not->toBeFalse()
        ->and($source)->toContain('type="submit" name="action" value="')
        ->and($source)->not->toContain("hidden('action'");
});

it('publishes the single named-view layout stylesheet', function (): void {
    $root = dirname(__DIR__, 4);
    $assets = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    $style = $assets['assets']['zoosper-admin-grid-workspace-single-view-style'] ?? null;

    expect($style)->toBeArray()
        ->and($style['path'] ?? null)->toBe('/asset/zoosper-admin-grid/css/grid-workspace-single-view.css?v=7c-ui2')
        ->and(is_file($root . '/packages/zoosper-admin-grid/resources/admin/css/grid-workspace-single-view.css'))->toBeTrue();
});
