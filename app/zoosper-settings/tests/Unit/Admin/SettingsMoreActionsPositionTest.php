<?php

declare(strict_types=1);

it('anchors the More actions panel to its disclosure instead of the viewport', function (): void {
    $root = dirname(__DIR__, 5);
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');

    expect($view)->toContain('.settings-more-actions{position:relative;margin-left:auto}')
        ->toContain('.settings-more-actions-panel{position:absolute;right:0;top:100%;')
        ->not->toContain('.settings-more-actions-panel{position:absolute;right:.6rem;');
});

it('retains the static narrow-screen action panel', function (): void {
    $root = dirname(__DIR__, 5);
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');

    expect($view)->toContain('.settings-more-actions-panel{position:static;grid-template-columns:1fr;width:100%;max-width:none;box-shadow:none}');
});
