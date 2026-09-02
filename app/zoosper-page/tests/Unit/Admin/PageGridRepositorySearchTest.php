<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageGridCriteria;
use Zoosper\Page\Admin\PageGridRepository;
use Zoosper\Pagination\Pager;

it('searches Page titles and slugs with distinct PDO placeholders', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $pdo->exec(
        'CREATE TABLE sites (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL
        )',
    );

    $pdo->exec(
        'CREATE TABLE pages (
            id INTEGER PRIMARY KEY,
            site_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            slug TEXT NOT NULL,
            status TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )',
    );

    $pdo->exec("INSERT INTO sites (id, name) VALUES (1, 'Main Website')");

    $insert = $pdo->prepare(
        'INSERT INTO pages
            (id, site_id, title, slug, status, updated_at)
         VALUES
            (:id, 1, :title, :slug, :status, :updated_at)',
    );

    $insert->execute([
        'id' => 1,
        'title' => 'About',
        'slug' => 'about',
        'status' => 'published',
        'updated_at' => '2026-09-02 00:00:00',
    ]);

    $insert->execute([
        'id' => 2,
        'title' => 'Home',
        'slug' => 'home',
        'status' => 'published',
        'updated_at' => '2026-09-02 00:00:00',
    ]);

    $result = (new PageGridRepository($pdo))->paginate(
        new PageGridCriteria(
            pager: new Pager(1, 20),
            query: 'about',
            status: '',
            siteId: null,
            sortBy: 'id',
            sortDir: 'desc',
        ),
    );

    expect($result->total)->toBe(1)
        ->and($result->items)->toHaveCount(1)
        ->and($result->items[0]['title'])->toBe('About')
        ->and($result->items[0]['slug'])->toBe('about')
        ->and($result->items[0]['site_name'])->toBe('Main Website');
});

it('keeps Page list and export search placeholders distinct', function (): void {
    $root = dirname(__DIR__, 5);

    $repository = (string) file_get_contents(
        $root . '/app/zoosper-page/src/Admin/PageGridRepository.php',
    );

    $export = (string) file_get_contents(
        $root . '/app/zoosper-page/src/Admin/PageGridExportSqlBuilder.php',
    );

    expect($repository)
        ->toContain('LIKE :query_title')
        ->toContain('LIKE :query_slug')
        ->not->toContain('LIKE :query OR')
        ->and($export)
        ->toContain('LIKE :export_search_title')
        ->toContain('LIKE :export_search_slug')
        ->not->toContain('LIKE :export_search OR');
});
