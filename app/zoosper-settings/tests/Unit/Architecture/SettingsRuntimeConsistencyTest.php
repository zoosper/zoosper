<?php

declare(strict_types=1);

it('resolves scoped values directly from the scoped repository after redirects', function (): void {
    $root = dirname(__DIR__, 5);
    $resolver = file_get_contents($root . '/app/zoosper-settings/src/Value/ScopedSettingValueResolver.php');
    $mutations = file_get_contents($root . '/app/zoosper-settings/src/Admin/SettingsMutationCoordinator.php');

    expect($resolver)->toContain('private ScopedSettingStoreInterface $scoped')
        ->toContain('$this->scoped->resolve($definition->path, $context)')
        ->and($mutations)->toContain('Response::redirect($this->urls->scope(');
});

it('does not flush the unrelated application cache from settings writes', function (): void {
    $root = dirname(__DIR__, 5);
    $writer = file_get_contents($root . '/app/zoosper-settings/src/Write/SectionSettingsWriter.php');
    $clearer = file_get_contents($root . '/app/zoosper-settings/src/Write/ScopedSettingClearer.php');
    $services = file_get_contents($root . '/app/zoosper-settings/config/services.php');

    foreach ([$writer, $clearer, $services] as $source) {
        expect($source)->not->toContain('CacheInterface')
            ->not->toContain('cache:clear')
            ->not->toContain('flush(')
            ->not->toContain('invalidate(');
    }
});

it('keeps project configuration immutable and scoped database configuration separate', function (): void {
    $root = dirname(__DIR__, 5);
    $static = file_get_contents($root . '/app/zoosper-core/src/Config/ConfigRepository.php');
    $writer = file_get_contents($root . '/app/zoosper-settings/src/Write/SectionSettingsWriter.php');

    expect($static)->toContain('final readonly class ConfigRepository')
        ->not->toContain('public function set(')
        ->and($writer)->toContain('ScopedSettingStoreInterface')
        ->toContain('$this->store->writeMany($normalised, $scope, $scopeKey)')
        ->not->toContain('ScopeConfigRepository')
        ->not->toContain('private PDO ');
});
