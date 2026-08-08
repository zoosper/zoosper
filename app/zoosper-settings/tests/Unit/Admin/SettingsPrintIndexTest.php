<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('prints a scope-aware category and field index', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('settings-print-index')
        ->toContain('Configuration catalogue')
        ->toContain('module-owned sections in the <?= $e($scopeLabel) ?> scope')
        ->toContain('array_sum(array_map(static fn($section)=>count($section->settings),$printSections))')
        ->toContain('.settings-print-index{display:block!important');
});

it('uses Settings-owned print source metadata and hides the screen result summary', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-print-url"')
        ->toContain('printUrl.textContent=location.href')
        ->toContain('#settings-result-summary{display:none!important}')
        ->not->toContain('data-copy-setting-value');
});
