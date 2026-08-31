<?php

declare(strict_types=1);

use Zoosper\Page\Repository\PageRevisionRepository;
use Zoosper\Page\Service\PageRevisionService;

function revisionPagingPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE page_revisions (id INTEGER PRIMARY KEY AUTOINCREMENT,page_id INTEGER,title TEXT,slug TEXT,content TEXT,status TEXT,content_format TEXT,content_json TEXT,meta_title TEXT,meta_description TEXT,meta_keywords TEXT,canonical_url TEXT,created_by INTEGER,created_at TEXT)');
    return $pdo;
}

it('pages revision history in descending order with an exact total', function (): void {
    $repository = new PageRevisionRepository($pdo = revisionPagingPdo());
    for ($i = 1; $i <= 23; $i++) {
        $repository->capture(7, ['title' => 'Revision ' . $i, 'slug' => 'r-' . $i, 'content' => 'x', 'status' => 'draft'], 1);
    }
    $service = new PageRevisionService($repository, 50);
    expect($service->historyCount(7))->toBe(23)
        ->and(array_map(static fn ($revision): int => $revision->id, $service->historyPage(7, 1, 10)))->toBe([23,22,21,20,19,18,17,16,15,14])
        ->and(array_map(static fn ($revision): int => $revision->id, $service->historyPage(7, 3, 10)))->toBe([3,2,1]);
});

it('removes duplicate Content guidance and makes revision history compact and CSP-safe', function (): void {
    $root = dirname(__DIR__, 5);
    $content = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/Form/PageContentSectionProvider.php');
    $responder = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/PageRevisionAdminResponder.php');
    $controller = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php');

    expect($content)->toContain("description: ''")
        ->not->toContain('The HTML fallback is sanitised and Editor.js JSON is validated on save.')
        ->and($responder)->toContain('private const PAGE_SIZE = 10')
        ->toContain('<details id="revision-history" class="card page-revision-history"')
        ->toContain('revision_page=')
        ->not->toContain('onclick=')
        ->not->toContain('confirm(')
        ->and($controller)->toContain('$request->query(\'revision_page\')')
        ->toContain('historyHtml($page, $revisionPage)');
});










