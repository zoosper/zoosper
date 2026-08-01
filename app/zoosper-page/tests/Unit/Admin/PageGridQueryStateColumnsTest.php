<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Page\Admin\PageGridQueryState;

test('explicit column visibility and order survive Page query normalisation', function (): void {
    $state = PageGridQueryState::fromQuery([
        'visible_columns' => ['title', 'status', 'site_name'],
        'column_order' => ['id', 'title', 'slug', 'status', 'site_name', 'actions'],
    ]);

    expect($state['visible_columns'])->toBe(['title', 'status', 'site_name']);
    expect($state['column_order'])->toBe(['id', 'title', 'slug', 'status', 'site_name', 'actions']);
});
