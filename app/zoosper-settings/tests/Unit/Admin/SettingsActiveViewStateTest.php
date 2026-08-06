<?php

declare(strict_types=1);

it('summarises active search, source view and density without values', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain('id="settings-active-state"')
        ->toContain("q?'Search: '+q:''")
        ->toContain("source!=='all'?'View: '")
        ->toContain("density.value!=='comfortable'?'Density: '")
        ->toContain(".filter(Boolean).join(' · ')");
});
