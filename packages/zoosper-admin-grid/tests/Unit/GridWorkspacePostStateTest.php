<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridWorkspacePostState;

test('workspace POST is reduced to recognised state fields', function (): void {
    $state = GridWorkspacePostState::fromPost([
        'filters' => ['status' => 'published'],
        'sort_by' => 'title',
        'sort_dir' => 'asc',
        'page_size' => '50',
        'visible_columns' => ['id', 'title', 'actions'],
        'column_order' => ['title', 'id', 'actions'],
        'admin_user_id' => 999,
        'grid_key' => 'admin.audit',
        'redirect' => 'https://example.invalid',
    ]);

    expect($state)->toBe([
        'filters' => ['status' => 'published'],
        'sort_by' => 'title',
        'sort_dir' => 'asc',
        'page_size' => 50,
        'visible_columns' => ['id', 'title', 'actions'],
        'column_order' => ['title', 'id', 'actions'],
    ]);
});
