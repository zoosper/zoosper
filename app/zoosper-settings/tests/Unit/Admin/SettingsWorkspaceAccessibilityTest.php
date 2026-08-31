<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('provides connected tab and panel semantics with keyboard navigation', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('aria-controls="category-panel-')
        ->toContain('role="tabpanel"')
        ->toContain('aria-labelledby="category-tab-')
        ->toContain("['ArrowDown','ArrowUp','ArrowRight','ArrowLeft','Home','End']")
        ->toContain('t.tabIndex=active?0:-1');
});

it('remembers group state and supports direct section and group links', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('data-group-key')
        ->toContain("'zoosper.settings.group.'+group.dataset.groupKey")
        ->toContain('function revealHash()')
        ->toContain('target.scrollIntoView');
});

it('keeps scope and category navigation sticky while preserving mobile fallback', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('.settings-scope{position:sticky')
        ->toContain('.settings-nav{position:sticky;top:4.6rem')
        ->toContain('@media(max-width:850px)');
});










