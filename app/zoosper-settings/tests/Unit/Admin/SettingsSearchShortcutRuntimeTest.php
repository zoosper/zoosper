<?php

declare(strict_types=1);

it('supports Enter shortcuts from search and the open More actions panel', function (): void {
    $root=dirname(__DIR__,5);
    $view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($view)->toContain('const navigateSearchShortcut=event=>')
        ->toContain("event.key==='Enter'")
        ->toContain('matchingFields.length')
        ->toContain('const inSearch=event.target===input')
        ->toContain('inSearchActions=actionsDisclosure?.open&&actionsDisclosure.contains(event.target)')
        ->toContain("input.addEventListener('keydown',navigateSearchShortcut)")
        ->toContain("actionsDisclosure.addEventListener('keydown',navigateSearchShortcut)")
        ->toContain('event.shiftKey?-1:1');
});

it('does not hijack Enter outside search navigation contexts', function (): void {
    $root=dirname(__DIR__,5);
    $view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($view)->toContain('if(!inSearch&&!inSearchActions)return')
        ->toContain('event.preventDefault();event.stopPropagation()');
});
