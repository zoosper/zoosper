<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;

it('provides an immutable numeric Page route parameter for canonical Admin actions', function (): void {
    $request = (new Request('GET', '/admin/pages/42/edit'))->withRouteParams(['id' => '42']);

    expect($request->routeParam('id'))->toBe('42')
        ->and($request->query('id'))->toBeNull();
});

it('keeps the legacy query identifier available during compatibility cutover', function (): void {
    $request = new Request('GET', '/admin/pages/edit', query: ['id' => '42']);

    expect($request->routeParam('id'))->toBeNull()
        ->and($request->query('id'))->toBe('42');
});










