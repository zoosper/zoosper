<?php

declare(strict_types=1);

it('synchronises only allowlisted workspace state into the current URL', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('const syncWorkspaceUrl=()=>')
        ->toContain('const url=new URL(buildWorkspaceUrl())')
        ->toContain("history.replaceState(null,'',url.pathname+url.search+url.hash)")
        ->toContain("['q','view','module','density'].forEach")
        ->not->toContain("url.searchParams.set('settings'");
});

it('updates link state after search, source, module and density changes', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain("input.addEventListener('input',()=>{applyFilters();syncWorkspaceUrl()})")
        ->toContain('applyFilters();syncWorkspaceUrl()')
        ->toContain("urlState.textContent=")
        ->toContain("Workspace state saved in link");
});

it('reapplies validated URL state during browser history navigation', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain("window.addEventListener('popstate',applyWorkspaceUrl)")
        ->not->toContain('id="settings-apply-url-state"')
        ->toContain("linkState.textContent='Applied workspace state from link'");
});
