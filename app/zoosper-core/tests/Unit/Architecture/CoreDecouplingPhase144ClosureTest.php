<?php

declare(strict_types=1);

it('keeps feature-module decoupling adapters available as safe no-ops', function (): void {
    $pageAdapterClass = 'Zoosper\\Page\\Routing\\PageFallbackHandlerAdapter';
    $siteAdapterClass = 'Zoosper\\Site\\Site\\SiteContextProviderAdapter';

    expect(class_exists($pageAdapterClass))->toBeTrue();
    expect(class_exists($siteAdapterClass))->toBeTrue();

    $pageAdapter = new $pageAdapterClass();
    $siteAdapter = new $siteAdapterClass();

    expect($pageAdapter)->toBeInstanceOf(Zoosper\Core\Routing\FallbackHandlerInterface::class);
    expect($pageAdapter->supports(new stdClass()))->toBeFalse();
    expect($pageAdapter->handle(new stdClass()))->toBeNull();

    expect($siteAdapter)->toBeInstanceOf(Zoosper\Core\Site\SiteContextProviderInterface::class);
    expect($siteAdapter->resolve(new stdClass()))->toBeNull();
});

it('keeps Phase 1.44 closure audit tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-feature-module-decoupling-adapters.php')->toBeFile();
    expect($root . '/tools/audit-core-downstream-after-phase-144.php')->toBeFile();
    expect($root . '/tools/audit-core-decoupling-phase-144-closure.php')->toBeFile();
});

it('documents that runtime cutover has not happened in Phase 1.44', function (): void {
    $root = dirname(__DIR__, 5);
    $doc = $root . '/docs/development/core-decoupling-phase-1.44-closure.md';

    expect($doc)->toBeFile();
    $contents = (string) file_get_contents($doc);
    expect($contents)->toContain('Runtime fallback is not rewired');
    expect($contents)->toContain('Runtime site context binding is not changed');
});
