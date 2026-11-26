<?php

declare(strict_types=1);

use Zoosper\Site\Infrastructure\DatabaseSiteLookup;
use Zoosper\Site\Repository\SiteRepository;

it('resolves repository hosts and codes without changing fallback behavior', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE sites (id INTEGER PRIMARY KEY, code TEXT, name TEXT, status TEXT, base_url TEXT)');
    $pdo->exec('CREATE TABLE site_domains (site_id INTEGER, host TEXT)');
    $pdo->exec("INSERT INTO sites VALUES (1, 'main', 'Main', 'active', 'https://main.test'), (2, 'old', 'Old', 'inactive', '')");
    $pdo->exec("INSERT INTO site_domains VALUES (1, 'main.test'), (2, 'old.test')");
    $lookup = new DatabaseSiteLookup(new SiteRepository($pdo));

    foreach ([$lookup->findByHost('MAIN.TEST'), $lookup->findActiveByHost('MAIN.TEST'), $lookup->findByCode('main')] as $site) {
        expect($site)->not->toBeNull()
            ->and($site->id)->toBe(1)
            ->and($site->code)->toBe('main')
            ->and($site->name)->toBe('Main')
            ->and($site->baseUrl)->toBe('https://main.test')
            ->and($site->isActive)->toBeTrue();
    }

    expect($lookup->findByHost('old.test'))->toBeNull()
        ->and($lookup->findActiveByHost('missing.test'))->toBeNull()
        ->and($lookup->findByCode('missing'))->toBeNull()
        ->and($lookup->findByCode('old')?->isActive)->toBeTrue()
        ->and($lookup->findDefault())->toBeNull();
});