<?php

declare(strict_types=1);

it('keeps the Site lookup service binding in the Site module services config', function (): void {
    $root = dirname(__DIR__, 5);
    $services = file_get_contents($root . '/app/zoosper-site/config/services.php') ?: '';

    expect($services)->toContain('SiteLookupInterface::class');
    expect($services)->toContain('DatabaseSiteLookup');
    expect($services)->toContain('SiteRepository::class');
});

it('keeps the Site database lookup adapter out of core service ownership', function (): void {
    $root = dirname(__DIR__, 5);
    $coreServices = file_get_contents($root . '/app/zoosper-core/config/services.php') ?: '';

    expect($coreServices)->not->toContain('DatabaseSiteLookup');
    expect($coreServices)->not->toContain('Zoosper\Site\Infrastructure\DatabaseSiteLookup');
});

it('does not reintroduce direct Site module references into core Site runtime source', function (): void {
    $root = dirname(__DIR__, 5);
    $coreSiteDir = $root . '/app/zoosper-core/src/Site';
    $violations = [];

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($coreSiteDir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $relative = str_replace($root . '/', '', $file->getPathname());
        $source = file_get_contents($file->getPathname()) ?: '';

        foreach (['Zoosper\Site\\', 'SiteRepository', 'DbSite'] as $needle) {
            if (str_contains($source, $needle)) {
                $violations[] = $relative . ' contains ' . $needle;
            }
        }
    }

    expect($violations)->toBe([]);
});










