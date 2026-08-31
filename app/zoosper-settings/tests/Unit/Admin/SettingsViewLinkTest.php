<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('supports copying and clearing the current workspace target', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-copy-view"')
        ->toContain('id="settings-clear-target"')
        ->toContain('const url=buildWorkspaceUrl()')
        ->toContain("history.replaceState(null,'',location.pathname+location.search)")
        ->not->toContain('data-copy-setting-value');
});










