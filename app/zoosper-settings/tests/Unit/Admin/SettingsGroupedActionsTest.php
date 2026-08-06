<?php

declare(strict_types=1);

it('groups secondary operations into three labelled regions', function (): void {
    $root=dirname(__DIR__,5);
    $view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');

    expect($view)->toContain('Search results</strong>')
        ->toContain('Share and output</strong>')
        ->toContain('Section display</strong>')
        ->toContain('aria-labelledby="settings-action-search-title"')
        ->toContain('aria-labelledby="settings-action-share-title"')
        ->toContain('aria-labelledby="settings-action-display-title"');
});

it('retains every secondary action exactly once', function (): void {
    $root=dirname(__DIR__,5);
    $view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');

    foreach (['settings-previous-match','settings-next-match','settings-copy-view','settings-clear-target','settings-print','settings-expand-all','settings-collapse-all'] as $id) {
        expect(substr_count($view, 'id="'.$id.'"'))->toBe(1);
    }
});

it('uses a bounded three-column desktop panel and one-column mobile panel', function (): void {
    $root=dirname(__DIR__,5);
    $view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');

    expect($view)->toContain('grid-template-columns:repeat(3,minmax(10rem,1fr))')
        ->toContain('width:min(38rem,calc(100vw - 2rem))')
        ->toContain('.settings-more-actions-panel{position:static;grid-template-columns:1fr;width:100%');
});
