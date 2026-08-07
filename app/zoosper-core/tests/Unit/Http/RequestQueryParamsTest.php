<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;

it('exposes the immutable request query map without reading globals', function (): void {
    $request = new Request('GET', '/admin/pages', query: ['q' => 'home', 'page' => '2']);
    expect($request->queryParams())->toBe(['q' => 'home', 'page' => '2'])
        ->and($request->query('q'))->toBe('home');
});
