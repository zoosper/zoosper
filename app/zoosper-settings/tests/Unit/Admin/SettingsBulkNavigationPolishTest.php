<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('uses canonical category order and remembers category navigation', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('private const CATEGORY_ORDER = [\'general\', \'communication\', \'content\', \'design\', \'commerce\', \'security\', \'advanced\']')
        ->toContain("localStorage.setItem('zoosper.settings.category',category)")
        ->toContain("localStorage.getItem('zoosper.settings.category')")
        ->toContain('settings-nav-title');
});

it('keeps the search control full width in its workspace column', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('grid-template-columns:minmax(0,1fr) minmax(18rem,30rem)')
        ->toContain('.settings-search{width:100%');
});
