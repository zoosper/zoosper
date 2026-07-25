<?php

declare(strict_types=1);

use Zoosper\Core\Site\NullSiteLookup;
use Zoosper\Core\Site\ResolvedSite;
use Zoosper\Core\Site\SiteLookupInterface;

it('keeps core site runtime files free from direct Site module namespaces', function (): void {
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

        if (str_contains($source, 'Zoosper\\Site\\')) {
            $violations[] = $relative;
        }
    }

    expect($violations)->toBe([]);
});

it('keeps SiteContextResolver behind the SiteLookupInterface boundary', function (): void {
    $root = dirname(__DIR__, 5);
    $source = file_get_contents($root . '/app/zoosper-core/src/Site/SiteContextResolver.php') ?: '';

    expect($source)->toContain('SiteLookupInterface');
    expect($source)->not->toContain('SiteRepository');
    expect($source)->not->toContain('DbSite');
    expect($source)->not->toContain('Zoosper\\Site\\');
});

it('keeps SiteLookupInterface compatible with active host lookup', function (): void {
    expect(interface_exists(SiteLookupInterface::class))->toBeTrue();
    expect(method_exists(SiteLookupInterface::class, 'findByHost'))->toBeTrue();
    expect(method_exists(SiteLookupInterface::class, 'findActiveByHost'))->toBeTrue();
    expect(method_exists(SiteLookupInterface::class, 'findByCode'))->toBeTrue();
    expect(method_exists(SiteLookupInterface::class, 'findDefault'))->toBeTrue();
});

it('keeps NullSiteLookup as a safe no-op for every lookup method', function (): void {
    $lookup = new NullSiteLookup();

    expect($lookup)->toBeInstanceOf(SiteLookupInterface::class);
    expect($lookup->findByHost('example.test'))->toBeNull();
    expect($lookup->findActiveByHost('example.test'))->toBeNull();
    expect($lookup->findByCode('default'))->toBeNull();
    expect($lookup->findDefault())->toBeNull();
});

it('retains resolved site fields required by DB-backed site contexts', function (): void {
    $site = new ResolvedSite(
        id: 7,
        code: 'nz',
        name: 'New Zealand Store',
        baseUrl: 'https://nz.example',
        isActive: true,
        websiteCode: 'anz',
        websiteName: 'ANZ Website',
        storeCode: 'nz',
        storeName: 'NZ Store',
        storeViewCode: 'nz_en',
        storeViewName: 'NZ English',
        locale: 'en_NZ',
        currency: 'NZD',
        pathPrefix: '/nz',
    );

    expect($site->id)->toBe(7);
    expect($site->websiteCode)->toBe('anz');
    expect($site->storeCode)->toBe('nz');
    expect($site->storeViewCode)->toBe('nz_en');
    expect($site->locale)->toBe('en_NZ');
    expect($site->currency)->toBe('NZD');
    expect($site->pathPrefix)->toBe('/nz');
});
