<?php

declare(strict_types=1);

it('keeps Theme Core-owned and wires both declared settings into runtime services', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode(file_get_contents($root . '/app/zoosper-theme/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $services = file_get_contents($root . '/app/zoosper-theme/config/services.php');
    $runtime = file_get_contents($root . '/app/zoosper-theme/src/Config/TemplateRuntimeConfig.php');
    expect($composer['require'])->toHaveKey('zoosper/core')->not->toHaveKey('zoosper/settings')
        ->and($services)->toContain('TemplateRuntimeConfig::class')
        ->toContain('ScopeConfigRepository::class')
        ->and($runtime)->toContain("'template.engine'")
        ->toContain("'template.template_cache_path'");
});

it('keeps the engine registry pluggable and the Theme catalogue read-only', function (): void {
    $root = dirname(__DIR__, 5);
    $registry = file_get_contents($root . '/app/zoosper-theme/src/Template/Engine/TemplateEngineRegistry.php');
    $settings = file_get_contents($root . '/app/zoosper-theme/config/admin_settings.php');
    expect($registry)->toContain('public function __construct(TemplateEngineInterface ...$engines)')->toContain('public function prioritise(array $priority): self')
        ->and(substr_count($settings, "'read_only' => true"))->toBe(2);
});
