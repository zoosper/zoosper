<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Page\Admin\PageGridQueryState;
use Zoosper\Page\Admin\PageGridWorkspace;

test('Pages grid identity and action are server-owned constants', function (): void {
    expect(PageGridWorkspace::GRID_KEY)->toBe('admin.pages');
    expect(PageGridWorkspace::ACTION)->toBe('/admin/pages');
});

test('Pages query becomes one resolved workspace state shape', function (): void {
    $state = PageGridQueryState::fromQuery([
        'q' => 'landing',
        'status' => 'published',
        'site_id' => ['4', '9'],
        'sort' => 'title',
        'dir' => 'asc',
        'page_size' => '50',
        'visible_columns' => ['id', 'title', 'actions'],
        'column_order' => ['title', 'id', 'actions'],
        'ignored' => 'discard',
    ]);

    expect($state)->toBe([
        'filters' => [
            'q' => 'landing',
            'title' => '',
            'slug' => '',
            'status' => 'published',
            'site_id' => ['4', '9'],
        ],
        'sort_by' => 'title',
        'sort_dir' => 'asc',
        'page_size' => 50,
        'visible_columns' => ['id', 'title', 'actions'],
        'column_order' => ['title', 'id', 'actions'],
    ]);
    expect(PageGridQueryState::bookmarkId(['bookmark_id' => '7']))->toBe(7);
    expect(PageGridQueryState::bookmarkId(['bookmark_id' => '0']))->toBeNull();
});
