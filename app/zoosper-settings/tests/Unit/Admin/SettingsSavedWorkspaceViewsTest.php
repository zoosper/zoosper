<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('provides accessible saved-view controls and keeps state value-free', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('id="settings-saved-view"')
        ->toContain('id="settings-save-view"')
        ->toContain('id="settings-delete-view" disabled')
        ->toContain('id="settings-saved-view-state"')
        ->not->toContain('data-copy-setting-value')
        ->not->toContain('data-setting-value');
});
