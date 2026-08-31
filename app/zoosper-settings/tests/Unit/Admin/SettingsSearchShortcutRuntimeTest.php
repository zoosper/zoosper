<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('supports Enter shortcuts from search and the open More actions panel', function (): void {
    $root=dirname(__DIR__,5);
    $view = settingsPresentationBundle($root);

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
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('if(!inSearch&&!inSearchActions)return')
        ->toContain('event.preventDefault();event.stopPropagation()');
});










