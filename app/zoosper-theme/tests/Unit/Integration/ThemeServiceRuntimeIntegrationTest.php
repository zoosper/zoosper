<?php

declare(strict_types=1);

it('uses runtime configuration for cache path engine binding and registry priority', function (): void {
    $root = dirname(__DIR__, 5);
    $services = file_get_contents($root . '/app/zoosper-theme/config/services.php');

    expect($services)->toContain('TemplateRuntimeConfig::class')
        ->toContain('->cacheDirectory()')
        ->toContain("->engine() === 'php'")
        ->toContain('->prioritise([$services->get(TemplateRuntimeConfig::class)->engine(), \'latte\', \'php\'])');
});

it('keeps frontend and admin renderers on the same pluggable registry service', function (): void {
    $root = dirname(__DIR__, 5);
    $services = file_get_contents($root . '/app/zoosper-theme/config/services.php');

    expect(substr_count($services, '$services->get(TemplateEngineRegistry::class)'))->toBe(2)
        ->and($services)->toContain("'theme.frontend_template_renderer'")
        ->toContain("'theme.admin_template_renderer'");
});
