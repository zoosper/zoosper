<?php

declare(strict_types=1);

use Zoosper\Core\Site\NullSiteLookup;
use Zoosper\Core\Site\ResolvedSite;
use Zoosper\Core\Site\SiteLookupInterface;
use Zoosper\Site\Infrastructure\DatabaseSiteLookup;

it('keeps the site lookup contract core-owned', function (): void {
    $root = dirname(__DIR__, 5);
    $source = file_get_contents($root . '/app/zoosper-core/src/Site/SiteLookupInterface.php');

    expect(interface_exists(SiteLookupInterface::class))->toBeTrue();
    expect($source)->not->toContain('Zoosper\Site\');
});

it('provides a safe null site lookup implementation', function (): void {
    $lookup = new NullSiteLookup();

    expect($lookup)->toBeInstanceOf(SiteLookupInterface::class);
    expect($lookup->findByHost('example.test'))->toBeNull();
    expect($lookup->findByCode('default'))->toBeNull();
    expect($lookup->findDefault())->toBeNull();
});

it('keeps resolved site as a core-owned immutable value object', function (): void {
    $site = new ResolvedSite(1, 'default', 'Default Site', 'https://example.test', true);

    expect($site->id)->toBe(1);
    expect($site->code)->toBe('default');
    expect($site->name)->toBe('Default Site');
    expect($site->baseUrl)->toBe('https://example.test');
    expect($site->isActive)->toBeTrue();
});

it('keeps the database-backed site lookup adapter in the site module', function (): void {
    expect(class_exists(DatabaseSiteLookup::class))->toBeTrue();
});










