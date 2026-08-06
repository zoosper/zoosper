<?php

declare(strict_types=1);

it('resolves scoped values directly from the scoped repository after redirects', function (): void {
    $root = dirname(__DIR__, 5);
    $resolver = file_get_contents($root . '/app/zoosper-settings/src/Value/ScopedSettingValueResolver.php');
    $controller = file_get_contents($root . '/app/zoosper-settings/src/Controller/SettingsCatalogueController.php');

    expect($resolver)->toContain('private ScopeConfigRepository $scoped')
        ->toContain('$this->scoped->getWithSource($definition->path, $context)')
        ->and($controller)->toContain('Response::redirect($this->scopeUrl(');
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
        ->and($writer)->toContain('ScopeConfigRepository')
        ->toContain('$this->repository->set($path, $scope, $scopeKey, $value)');
});
