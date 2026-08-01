<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Http;

use Zoosper\Core\Http\Request;

test('request keeps list query parameters without scalar conversion', function (): void {
    $request = new Request('GET', '/admin/pages', query: [
        'status' => 'draft',
        'visible_columns' => ['title', 'slug'],
        'column_order' => ['id', 'title', 'slug', 'actions'],
    ]);

    expect($request->query('status'))->toBe('draft');
    expect($request->query('visible_columns'))->toBeNull();
    expect($request->queryList('visible_columns'))->toBe(['title', 'slug']);
    expect($request->queryParams()['column_order'])->toBe(['id', 'title', 'slug', 'actions']);
});
