<?php

declare(strict_types=1);

it('keeps Editor independent of Settings while consuming Core scoped configuration', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode(file_get_contents($root . '/app/zoosper-editor/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $services = file_get_contents($root . '/app/zoosper-editor/config/services.php');
    $runtime = file_get_contents($root . '/app/zoosper-editor/src/Config/ContentEditorRuntimeConfig.php');

    expect($composer['require'])->toHaveKey('zoosper/core')->not->toHaveKey('zoosper/settings')
        ->and($services)->toContain('ContentEditorRuntimeConfig::class')
        ->toContain('ScopeConfigRepository::class')
        ->and($runtime)->toContain("'editor.default_editor'")
        ->toContain("'editor.fallback_editor'");
});

it('publishes read-only built-in choices without restricting module-owned editor codes', function (): void {
    $root = dirname(__DIR__, 5);
    $settings = file_get_contents($root . '/app/zoosper-admin/config/admin_settings.php');
    $registry = file_get_contents($root . '/app/zoosper-editor/src/ContentEditorRegistry.php');

    expect(substr_count($settings, "'read_only' => true"))->toBe(2)
        ->and($registry)->toContain('public function __construct(ContentEditorInterface ...$editors)')
        ->toContain('public function register(ContentEditorInterface $editor): void');
});










