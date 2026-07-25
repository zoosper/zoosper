<?php

declare(strict_types=1);

use PDO;
use Zoosper\Page\Admin\PageMomentum\PageMomentumDashboardFactsProvider;

it('returns read-only dashboard facts from available page data', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec(
        <<<'SQL'
        CREATE TABLE pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            status TEXT NOT NULL,
            meta_title TEXT NULL,
            meta_description TEXT NULL
        )
        SQL,
    );

    $pdo->exec(
        <<<'SQL'
        INSERT INTO pages (title, status, meta_title, meta_description) VALUES
        ('Home', 'published', 'Home SEO', 'Home description'),
        ('About', 'draft', '', ''),
        ('Contact', 'disabled', NULL, NULL)
        SQL,
    );

    $provider = new PageMomentumDashboardFactsProvider($pdo);
    $facts = $provider->factsAsArray();

    $values = [];
    foreach ($facts as $fact) {
        $values[$fact['key']] = $fact['value'];
    }

    expect($values['total_pages'])->toBe(3)
        ->and($values['published_pages'])->toBe(1)
        ->and($values['draft_pages'])->toBe(1)
        ->and($values['disabled_pages'])->toBe(1)
        ->and($values['missing_seo_title'])->toBe(2)
        ->and($values['missing_seo_description'])->toBe(2);
});

it('fails soft when optional tables are missing', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $provider = new PageMomentumDashboardFactsProvider($pdo);
    $facts = $provider->factsAsArray();

    expect($facts)->not->toBeEmpty();

    foreach ($facts as $fact) {
        expect($fact['value'])->toBeInt();
    }
});
