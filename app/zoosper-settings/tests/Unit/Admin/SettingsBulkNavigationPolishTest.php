<?php

declare(strict_types=1);

it('uses canonical category order and remembers category navigation', function (): void {
    $root = dirname(__DIR__, 5);
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($view)->toContain('$categoryOrder = [\'general\', \'communication\', \'content\', \'design\', \'commerce\', \'security\', \'advanced\']')
        ->toContain("localStorage.setItem('zoosper.settings.category',category)")
        ->toContain("localStorage.getItem('zoosper.settings.category')")
        ->toContain('settings-nav-title');
});

it('keeps the search control full width in its workspace column', function (): void {
    $root = dirname(__DIR__, 5);
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($view)->toContain('grid-template-columns:minmax(0,1fr) minmax(18rem,30rem)')
        ->toContain('.settings-search{width:100%');
});
