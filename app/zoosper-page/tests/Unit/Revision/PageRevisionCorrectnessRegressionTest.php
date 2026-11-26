<?php

declare(strict_types=1);

use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Repository\PageRevisionRepository;
use Zoosper\Page\Service\PageRevisionService;

function revisionCorrectnessDatabase(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT, site_id INTEGER, title TEXT, slug TEXT, content TEXT, status TEXT, created_by INTEGER, updated_by INTEGER, created_at TEXT, updated_at TEXT, published_at TEXT)');
    $pdo->exec('CREATE TABLE page_revisions (id INTEGER PRIMARY KEY AUTOINCREMENT, page_id INTEGER, title TEXT, slug TEXT, content TEXT, status TEXT, content_format TEXT, content_json TEXT, meta_title TEXT, meta_description TEXT, meta_keywords TEXT, canonical_url TEXT, created_by INTEGER, created_at TEXT)');
    return $pdo;
}

it('returns complete non-overlapping revision pages at the API maximum size', function (): void {
    $pdo = revisionCorrectnessDatabase();
    $repository = new PageRevisionRepository($pdo);
    for ($i = 1; $i <= 120; $i++) {
        $repository->capture(1, ['title' => 'T', 'slug' => 's', 'content' => 'c', 'status' => 'draft'], 1);
    }
    $service = new PageRevisionService($repository, 200);
    $first = $service->historyPage(1, 1, 100);
    $second = $service->historyPage(1, 2, 100);
    expect($first)->toHaveCount(100)->and($second)->toHaveCount(20)
        ->and(array_intersect(array_column($first, 'id'), array_column($second, 'id')))->toBe([]);
});

it('restores archived revision snapshots with their original status', function (): void {
    $pdo = revisionCorrectnessDatabase();
    $pages = new PageRepository($pdo);
    $id = $pages->create(1, 'Archived', 'archived', 'old', 'archived', 1);
    $revisions = new PageRevisionService(new PageRevisionRepository($pdo));
    $revisionId = $revisions->capturePage($pages->findById($id), 1);
    $pages->restoreArchived($id, 1);
    $revisions->restore($pages->findById($id), $revisionId, 1, $pages);
    expect($pages->findById($id)->status)->toBe('archived');
});