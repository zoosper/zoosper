<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use PDO;
use Zoosper\Grid\GridCriteria;
use Zoosper\Page\Admin\PageGridDataSource;
use Zoosper\Page\Admin\PageGridDefinition;
use Zoosper\Page\Admin\PageGridRepository;

test('shared page grid sorting is honoured and safely allow-listed', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE sites (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY, site_id INTEGER, title TEXT, slug TEXT, status TEXT, updated_at TEXT)');
    $pdo->exec("INSERT INTO sites (id, name) VALUES (1, 'Main')");
    $pdo->exec("INSERT INTO pages VALUES (1, 1, 'Zulu', 'zulu', 'draft', '2026-01-01')");
    $pdo->exec("INSERT INTO pages VALUES (2, 1, 'Alpha', 'alpha', 'published', '2026-02-01')");

    $definition = (new PageGridDefinition())->build();
    $source = new PageGridDataSource(new PageGridRepository($pdo));
    $ascending = $source->paginate(GridCriteria::fromValues([
        'sort' => 'title', 'dir' => 'asc', 'page_size' => 20,
    ], $definition));
    $malicious = $source->paginate(GridCriteria::fromValues([
        'sort' => 'title; DROP TABLE pages', 'dir' => 'asc', 'page_size' => 20,
    ], $definition));

    expect(array_column($ascending->items, 'title'))->toBe(['Alpha', 'Zulu']);
    expect($malicious->items)->toHaveCount(2);
    expect((int) $pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn())->toBe(2);
});

test('page index template renders the shared grid output instead of a manual table', function (): void {
    $basePath = dirname(__DIR__, 5);
    $template = (string) file_get_contents(
        $basePath . '/app/zoosper-page/resources/views/admin/pages/index.php',
    );

    expect($template)->toContain('$gridHtml');
    expect($template)->not->toContain('<table>');
    expect($template)->not->toContain('page-filters.php');
    expect($template)->not->toContain('pagination.php');
});
