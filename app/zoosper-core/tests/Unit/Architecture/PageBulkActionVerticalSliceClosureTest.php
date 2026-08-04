<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageGridBulkActions;

it('locks the completed Pages bulk-action vertical slice', function (): void {
    $root = dirname(__DIR__, 5);
    $definitions = PageGridBulkActions::definitions();
    $browserIds = array_map(static fn ($definition): string => $definition->id, $definitions);
    $server = PageGridBulkActions::serverDefinitions();

    expect($browserIds)->toBe(['export.selected', 'page.publish']);
    expect($server)->toHaveCount(1);
    expect($server[0]->id)->toBe('page.publish');

    $routes = require $root . '/app/zoosper-page/config/admin_routes.php';
    $matches = array_values(array_filter(
        $routes,
        static fn (array $route): bool =>
            ($route['method'] ?? null) === 'POST'
            && ($route['path'] ?? null) === '/admin/pages/bulk-action',
    ));
    expect($matches)->toHaveCount(1);
    expect($matches[0]['permission'])->toBe('page.manage');

    $assets = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    expect($assets['assets'])->toHaveKey('zoosper-admin-grid-server-mutation-script');
    expect($assets['assets']['zoosper-admin-grid-server-mutation-script']['path'])
        ->toContain('grid-server-mutation.js');
});

it('locks trusted actor, confirmation, CSRF, event and audit boundaries', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageBulkActionController.php');
    $browser = file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/js/grid-server-mutation.js');
    $effects = file_get_contents($root . '/app/zoosper-page/src/Admin/BulkAction/PagePublishSideEffects.php');

    expect($controller)->not->toBeFalse();
    expect($browser)->not->toBeFalse();
    expect($effects)->not->toBeFalse();
    expect($controller)->toContain(<<<'PHP'
new GridBulkActor($user->id, $user->email)
PHP);
    expect($controller)->not->toContain(<<<'PHP'
new GridBulkActor($form
PHP);
    expect($browser)->toContain("add('_csrf_token', token)");
    expect($browser)->toContain("add('confirmed_action', definition.id)");
    expect($effects)->toContain('PageEvents::PUBLISHED');
    expect($effects)->toContain("action: 'page.bulk_publish'");
});

it('contains no stale one-action Page manifest expectations', function (): void {
    $root = dirname(__DIR__, 5);
    $tests = glob($root . '/app/zoosper-page/tests/Unit/Admin/*.php') ?: [];
    $stale = [];

    foreach ($tests as $path) {
        $source = file_get_contents($path);
        if ($source !== false
            && str_contains($source, "PageGridBulkActions::definitions()")
            && str_contains($source, "toBe(['export.selected'])")) {
            $stale[] = basename($path);
        }
    }

    expect($stale)->toBe([]);
});
