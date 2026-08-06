<?php

declare(strict_types=1);

it('closes Theme runtime adoption without coupling Theme to Settings or changing theme-code ownership', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode(file_get_contents($root . '/app/zoosper-theme/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $themeConfig = file_get_contents($root . '/app/zoosper-theme/config/theme.php');
    $runtime = file_get_contents($root . '/app/zoosper-theme/src/Config/TemplateRuntimeConfig.php');

    expect($composer['require'])->not->toHaveKey('zoosper/settings')
        ->and($themeConfig)->toContain("env('THEME_CODE', 'default')")
        ->and($runtime)->toContain("'template.engine'")
        ->toContain("'template.template_cache_path'");
});

it('preserves the public variadic registry constructor and additive priority API', function (): void {
    $root = dirname(__DIR__, 5);
    $registry = file_get_contents($root . '/app/zoosper-theme/src/Template/Engine/TemplateEngineRegistry.php');

    expect($registry)->toContain('public function __construct(TemplateEngineInterface ...$engines)')
        ->toContain('public function prioritise(array $priority): self')
        ->not->toContain('public function __construct(array $priority');
});
