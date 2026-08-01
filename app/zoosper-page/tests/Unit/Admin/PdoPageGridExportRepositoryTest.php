<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use PDO;
use Zoosper\Page\Admin\PageGridExportCriteria;
use Zoosper\Page\Admin\PageGridExportSqlBuilder;
use Zoosper\Page\Admin\PdoPageGridExportRepository;

function exportRepositoryDatabase(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE sites (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY, title TEXT NOT NULL, slug TEXT NOT NULL, status TEXT NOT NULL, site_id INTEGER NOT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec("INSERT INTO sites (id, name) VALUES (4, 'Main Website'), (9, 'Wholesale Portal')");
    $pdo->exec("INSERT INTO pages (id, title, slug, status, site_id, created_at, updated_at) VALUES
        (1, 'Landing One', 'landing-one', 'published', 4, '2026-01-01', '2026-01-02'),
        (2, 'Draft Page', 'draft-page', 'draft', 4, '2026-01-03', '2026-01-04'),
        (3, 'Landing Two', 'landing-two', 'published', 9, '2026-01-05', '2026-01-06')");
    return $pdo;
}

test('repository streams named Site rows matching resolved filters', function (): void {
    $repository = new PdoPageGridExportRepository(
        exportRepositoryDatabase(),
        new PageGridExportSqlBuilder(),
    );

    $rows = iterator_to_array($repository->stream(new PageGridExportCriteria(
        search: 'Landing',
        status: 'published',
        siteIds: [9, 4],
        sortBy: 'title',
        sortDir: 'asc',
    )));

    expect(array_column($rows, 'title'))->toBe(['Landing One', 'Landing Two']);
    expect(array_column($rows, 'site_name'))->toBe(['Main Website', 'Wholesale Portal']);
});

test('repository does not inherit screen pagination', function (): void {
    $repository = new PdoPageGridExportRepository(
        exportRepositoryDatabase(),
        new PageGridExportSqlBuilder(),
    );

    $rows = iterator_to_array($repository->stream(new PageGridExportCriteria(
        search: '',
        status: '',
        siteIds: [],
        sortBy: 'id',
        sortDir: 'asc',
    )));

    expect(array_column($rows, 'id'))->toBe([1, 2, 3]);
});
