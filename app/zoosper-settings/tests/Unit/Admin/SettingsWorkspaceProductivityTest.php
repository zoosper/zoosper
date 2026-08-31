<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('provides reset-view and search keyboard shortcuts', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('id="settings-reset-view"')
        ->toContain("event.key===" . "'/'")
        ->toContain("event.key==='Escape'")
        ->toContain("localStorage.removeItem('zoosper.settings.sourceFilter')")
        ->toContain("localStorage.removeItem('zoosper.settings.group.'+group.dataset.groupKey)");
});

it('preserves section and group deep-link hashes while activating categories', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('function activate(category,focus=false,updateHash=true)')
        ->toContain('if(panel)activate(panel.dataset.categoryPanel,false,false)')
        ->toContain('if(updateHash)history.replaceState');
});

it('hides default scope value server-side and publishes filtered category counts', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('$scopeType === \'default\'')
        ->toContain('class="settings-hidden"')
        ->toContain('data-category-count')
        ->toContain('data-total=')
        ->toContain('badge.hidden=');
});










