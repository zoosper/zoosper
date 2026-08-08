<?php

declare(strict_types=1);

it('connects disclosure summaries to deterministic panel IDs', function (): void {
    $root=dirname(__DIR__,5);
    $view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($view)->toContain('id="settings-help-summary" aria-controls="settings-help-panel"')
        ->toContain('id="settings-help-panel"')
        ->toContain('id="settings-actions-summary" aria-controls="settings-actions-panel"')
        ->toContain('id="settings-actions-panel"');
});

it('moves focus into an opened panel and restores trigger focus on Escape', function (): void {
    $root=dirname(__DIR__,5);
    $view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($view)->toContain('const disclosureFocusable=panel=>')
        ->toContain('requestAnimationFrame(()=>disclosureFocusable(panel)?.focus())')
        ->toContain('closeFloatingPanels(null,true)')
        ->toContain('disclosureSummary(lastOpenedDisclosure)?.focus()');
});

it('keeps aria-expanded synchronised with native details state', function (): void {
    $root=dirname(__DIR__,5);
    $view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($view)->toContain("setAttribute('aria-expanded',String(panel.open))")
        ->toContain("setAttribute('aria-expanded','false')");
});
