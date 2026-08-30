<?php

declare(strict_types=1);

it('places saved views in the primary command row and fixes field identity', function (): void {
    $root=dirname(__DIR__,4);$source=str_replace("\r\n", "\n", (string) file_get_contents($root.'/packages/zoosper-admin-grid/src/GridCompactToolbarRenderer.php'));
    expect($source)->not->toBeFalse()
        ->and($source)->toContain('id="grid-workspace-view"')
        ->and($source)->toContain('name="bookmark_view"')
        ->and(strpos($source,'grid-compact-view-tools'))->toBeLessThan(strpos($source,"'</div>'\n            . '<div class=\"grid-compact-state\""));
});

it('does not compete with the established Filters and Columns script', function (): void {
    $root=dirname(__DIR__,4);$script=file_get_contents($root.'/packages/zoosper-admin-grid/resources/admin/js/grid-workspace-command-bar.js');
    expect($script)->not->toBeFalse()
        ->and($script)->not->toContain('toggles.forEach')
        ->and($script)->not->toContain('data-grid-command-bar-bound')
        ->and($script)->toContain("document.querySelectorAll('[data-grid-panel]')");
});

it('hides saved-view management when a page has no mutation form target', function (): void {
    $root=dirname(__DIR__,4);$script=file_get_contents($root.'/packages/zoosper-admin-grid/resources/admin/js/grid-workspace-command-bar.js');
    expect($script)->toContain('if(!settings){if(toggle)toggle.hidden=true;return;}');
});
