<?php

declare(strict_types=1);

it('keeps Settings application services behind the scoped persistence contract', function (): void {
    $root = dirname(__DIR__, 5);
    foreach ([
        'src/Value/ScopedSettingValueResolver.php',
        'src/Write/SectionSettingsWriter.php',
        'src/Write/ScopedSettingClearer.php',
    ] as $relative) {
        $source = (string) file_get_contents($root . '/app/zoosper-settings/' . $relative);
        expect($source)->toContain('ScopedSettingStoreInterface')
            ->not->toContain('ScopeConfigRepository')
            ->not->toContain('private PDO ');
    }
});
