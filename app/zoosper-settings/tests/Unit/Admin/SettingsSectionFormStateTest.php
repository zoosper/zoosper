<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('tracks section-level unsaved changes and disables idle actions', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('data-settings-form')
        ->toContain('data-form-status')
        ->toContain('data-reset-section disabled')
        ->toContain('data-save-section disabled')
        ->toContain("form.dataset.dirty=dirty?'true':'false'")
        ->toContain("status.textContent=dirty?'Unsaved changes':'No unsaved changes'");
});

it('supports section reset and protects dirty navigation', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain("reset.addEventListener('click'")
        ->toContain('form.reset();form.dataset.dirty=')
        ->toContain('refresh()')
        ->toContain("window.addEventListener('beforeunload'")
        ->toContain("form.dataset.submitting='true'");
});

it('reports visible results for the active category only', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('function updateSummary()')
        ->toContain("panels.find(panel=>!panel.hidden)")
        ->toContain("+' in '+label")
        ->not->toContain("summary.textContent=visibleFields+");
});










