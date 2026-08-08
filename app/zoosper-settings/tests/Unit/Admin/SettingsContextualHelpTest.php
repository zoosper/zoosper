<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('moves persistent explanatory text into an accessible help disclosure', function (): void {
    $root=dirname(__DIR__,5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('<details class="settings-help">')
        ->toContain('<summary id="settings-help-summary" aria-controls="settings-help-panel" aria-label="About Settings">?</summary>')
        ->toContain('class="settings-help-panel"')
        ->toContain('module-owned section')
        ->toContain('Current scope:')
        ->not->toContain('<p class="settings-summary">');
});

it('keeps scope compact and shows reset only outside Default scope', function (): void {
    $root=dirname(__DIR__,5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('aria-label="Settings scope"')
        ->toContain('scopeType!==')
        ->toContain("'default'")
        ->toContain('Reset to Default')
        ->not->toContain('<label>Scope<select');
});
it('consolidates visible workspace feedback into one compact status line', function (): void {
    $root=dirname(__DIR__,5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('id="settings-workspace-status"')
        ->toContain('id="settings-active-state"')
        ->toContain('id="settings-result-summary"')
        ->toContain('settings-workspace-status span:not(:empty)+span:not(:empty):before')
        ->toContain('id="settings-link-state" class="settings-hidden"')
        ->toContain('id="settings-url-state" class="settings-hidden"');
});
