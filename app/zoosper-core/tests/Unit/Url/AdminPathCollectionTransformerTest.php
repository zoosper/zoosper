<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Url\AdminPathCollectionTransformer;
use Zoosper\Core\Url\AdminUrlGenerator;

function adminPathTransformer(string $base = '/control-centre'): AdminPathCollectionTransformer
{
    return new AdminPathCollectionTransformer(new AdminUrlGenerator(
        ConfigRepository::fromArray(['admin' => ['base_path' => $base]]),
    ));
}

it('expands route paths without mutating unrelated route metadata', function (): void {
    $routes = [[
        'method' => 'POST', 'path' => '/admin/pages/{id:\\d+}',
        'controller' => 'PageController', 'action' => 'save',
        'permission' => ['page.manage'],
    ], [
        'method' => 'GET', 'path' => '/api/v1/health', 'public' => true,
    ]];

    $expanded = adminPathTransformer()->routes($routes);

    expect($expanded[0]['path'])->toBe('/control-centre/pages/{id:\\d+}')
        ->and($expanded[0]['method'])->toBe('POST')
        ->and($expanded[0]['permission'])->toBe(['page.manage'])
        ->and($expanded[1])->toBe($routes[1])
        ->and($routes[0]['path'])->toBe('/admin/pages/{id:\\d+}');
});

it('expands menu URLs while preserving query strings and fragments', function (): void {
    $items = [[
        'code' => 'pages', 'label' => 'Pages',
        'url' => '/admin/pages?status=draft#grid', 'permission' => 'page.manage',
    ]];

    $expanded = adminPathTransformer()->menu($items);

    expect($expanded[0]['url'])->toBe('/control-centre/pages?status=draft#grid')
        ->and($expanded[0]['code'])->toBe('pages');
});

it('keeps the default admin path byte compatible', function (): void {
    $routes = [['path' => '/admin/settings', 'method' => 'GET']];
    expect(adminPathTransformer('/admin')->routes($routes))->toBe($routes);
});

it('passes declarations without the target field through for loader validation', function (): void {
    expect(adminPathTransformer()->routes([['method' => 'GET']]))->toBe([['method' => 'GET']]);
});

it('rejects non-absolute route and menu paths', function (array $rows, string $method): void {
    expect(fn () => adminPathTransformer()->{$method}($rows))
        ->toThrow(\InvalidArgumentException::class);
})->with([
    'relative route' => [[['path' => 'admin/pages']], 'routes'],
    'empty menu URL' => [[['url' => '']], 'menu'],
    'non-string route' => [[['path' => 42]], 'routes'],
]);
