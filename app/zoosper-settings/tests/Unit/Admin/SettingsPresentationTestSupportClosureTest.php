<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('provides one reusable Settings presentation test bundle', function (): void {
    $root = dirname(__DIR__, 5);
    $bundle = settingsPresentationBundle($root);

    expect($bundle)->toContain('id="settings-workspace"')
        ->toContain('.settings-workspace')
        ->toContain("const savedViewsKey='zoosper.settings.savedViews'");
});

it('retires duplicated three-file setup from Settings Admin contract tests', function (): void {
    $root = dirname(__DIR__, 5);
    $files = glob($root . '/app/zoosper-settings/tests/Unit/Admin/*.php') ?: [];

    foreach ($files as $file) {
        if (in_array(basename($file), [
            'SettingsPresentationAssetClosureTest.php',
            'SettingsPresentationTestSupportClosureTest.php',
        ], true)) {
            continue;
        }
        $source = (string) file_get_contents($file);
        if (!str_contains($source, 'settingsPresentationBundle(')) {
            continue;
        }
        expect($source, basename($file))
            ->not->toContain("resources/assets/css/settings-workspace.css')")
            ->not->toContain("resources/assets/js/settings-workspace.js')");
    }
});
