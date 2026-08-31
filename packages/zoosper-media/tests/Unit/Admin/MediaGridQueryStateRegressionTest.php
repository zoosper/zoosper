<?php

declare(strict_types=1);

use Zoosper\AdminGrid\AdminCollectionGridQuery;
use Zoosper\AdminGrid\GridStateNormaliser;
use Zoosper\Core\Http\Request;
use Zoosper\Media\Admin\Grid\MediaGridSource;

it('applies the deployed Media URL to SQL criteria and visual-card state', function (): void {
    $pdo = new \PDO('sqlite::memory:');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE media_assets (id INTEGER PRIMARY KEY, uuid TEXT NOT NULL, filename TEXT NOT NULL, original_filename TEXT NOT NULL, mime_type TEXT NOT NULL, extension TEXT NOT NULL, size_bytes INTEGER NOT NULL, public_path TEXT NOT NULL, status TEXT NOT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec("INSERT INTO media_assets VALUES (1, 'one', 'stored-one.jpg', 'what-product.jpg', 'image/jpeg', 'jpg', 1024, '/media/one.jpg', 'active', '2026-08-20 10:00:00', '2026-08-20 10:00:00')");
    $pdo->exec("INSERT INTO media_assets VALUES (2, 'two', 'stored-two.png', 'other-product.png', 'image/png', 'png', 2048, '/media/two.png', 'active', '2026-08-20 11:00:00', '2026-08-20 11:00:00')");

    $source = new MediaGridSource($pdo);
    $definition = $source->definition();
    $request = new Request('GET', '/admin/media', query: [
        'page' => '1',
        'q' => 'what',
        'status' => '',
        'mime_type' => '',
        'extension' => '',
        'visible_columns' => ['original_filename', 'mime_type', 'extension', 'size_bytes', 'status'],
        'column_order' => ['id', 'preview', 'original_filename', 'mime_type', 'extension', 'size_bytes', 'status', 'created_at', 'actions'],
        'columns_submitted' => '1',
        'sort' => 'created_at',
        'dir' => 'desc',
    ]);

    $state = AdminCollectionGridQuery::values($request, $definition);
    $normaliser = new GridStateNormaliser();
    $normalised = $normaliser->normalise($state, $definition);
    $criteria = $normaliser->criteria($state, $definition);
    $page = $source->paginate($criteria);

    expect($normalised['filters'])->toBe(['q' => 'what'])
        ->and($normalised['sort_by'])->toBe('created_at')
        ->and($normalised['sort_dir'])->toBe('desc')
        ->and($normalised['visible_columns'])->toBe([
            'original_filename', 'mime_type', 'extension', 'size_bytes', 'status',
            'id', 'preview', 'actions',
        ])
        ->and($normalised['visible_columns'])->not->toContain('created_at')
        ->and($criteria->filters)->toBe(['q' => 'what'])
        ->and($page->total)->toBe(1)
        ->and($page->items)->toHaveCount(1)
        ->and($page->items[0]['original_filename'])->toBe('what-product.jpg');
});

it('uses distinct portable placeholders for both filename search predicates', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Admin/Grid/MediaGridSource.php');

    expect($source)->toContain('filename LIKE :filename_q')
        ->toContain('original_filename LIKE :original_filename_q')
        ->not->toContain('filename LIKE :q OR original_filename LIKE :q');
});











