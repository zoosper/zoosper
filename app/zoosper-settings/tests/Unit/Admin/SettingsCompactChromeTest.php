<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('keeps primary filters visible and moves secondary operations into a disclosure', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('settings-toolbar-primary')
        ->toContain('<details class="settings-more-actions">')
        ->toContain('<summary id="settings-actions-summary" aria-controls="settings-actions-panel">More actions</summary>')
        ->toContain('settings-more-actions-panel')
        ->toContain('id="settings-previous-match"')
        ->toContain('id="settings-print"')
        ->toContain('id="settings-expand-all"');
});

it('uses compact title, scope and toolbar spacing', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('margin-bottom:.65rem')
        ->toContain('padding:.55rem .7rem')
        ->toContain('top:4.6rem')
        ->toContain('.settings-muted:empty{display:none}');
});

it('uses a static action panel and scope bar on narrow screens', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('.settings-more-actions-panel{position:static;grid-template-columns:1fr;width:100%;max-width:none;box-shadow:none}')
        ->toContain('.settings-scope{position:static}');
});










