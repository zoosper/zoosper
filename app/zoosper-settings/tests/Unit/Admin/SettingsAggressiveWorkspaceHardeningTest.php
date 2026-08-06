<?php

declare(strict_types=1);

it('supports first and last search-result keyboard navigation', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain('const navigateSearchBoundary=event=>')
        ->toContain("['Home','End'].includes(event.key)")
        ->toContain("showMatch(event.key==='Home'?0:matchingFields.length-1)");
});

it('stores the selected search result as a value-free field fragment', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain("history.replaceState(null,'','#'+field.id)")
        ->not->toContain('data-setting-value');
});

it('closes More actions after one-shot operations but retains match navigation', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain("actionsDisclosure.addEventListener('click'")
        ->toContain("'settings-copy-view','settings-clear-target','settings-print','settings-expand-all','settings-collapse-all'")
        ->toContain('actionsDisclosure.open=false');
});

it('provides strong focus-visible treatment and closes overlays during form actions', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain('.settings-help summary:focus-visible')
        ->toContain('.settings-more-actions summary:focus-visible')
        ->toContain("form.addEventListener('submit',()=>{closeFloatingPanels(null);")
        ->toContain("reset.addEventListener('click',()=>{closeFloatingPanels(null);");
});
