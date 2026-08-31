<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('summarises active search, source view and density without values', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-active-state"')
        ->toContain("q?'Search: '+q:''")
        ->toContain("source!=='all'?'View: '")
        ->toContain("density.value!=='comfortable'?'Density: '")
        ->toContain(".filter(Boolean).join(' · ')");
});










