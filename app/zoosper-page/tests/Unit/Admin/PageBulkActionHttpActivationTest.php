<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

it('publishes the protected Page bulk endpoint with existing security contracts', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = file_get_contents(
        $root . '/app/zoosper-page/src/Admin/Controller/PageBulkActionController.php',
    );
    $routes = require $root . '/app/zoosper-page/config/admin_routes.php';

    expect($controller)->not->toBeFalse();
    expect($controller)->toContain("requirePermission('page.manage')");
    expect($controller)->toContain(<<<'PHP'
$form['_csrf_token']
PHP);
    expect($controller)->toContain(<<<'PHP'
new GridBulkActor($user->id, $user->email)
PHP);
    expect($controller)->toContain("\$this->adminUrls?->url('pages') ?? '/admin/pages'");

    $matches = array_values(array_filter(
        $routes,
        static fn (array $route): bool =>
            ($route['method'] ?? null) === 'POST'
            && ($route['path'] ?? null) === '/admin/pages/bulk-action',
    ));

    expect($matches)->toHaveCount(1);
    expect($matches[0]['permission'])->toBe('page.manage');
    expect($matches[0]['action'])->toBe('execute');
});

it('exposes Publish selected after protected endpoint activation', function (): void {
    $ids = array_map(
        static fn ($definition): string => $definition->id,
        \Zoosper\Page\Admin\PageGridBulkActions::definitions(),
    );

    expect($ids)->toBe(['export.selected', 'page.publish']);
});










