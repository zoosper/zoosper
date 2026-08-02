<?php

declare(strict_types=1);

it('wires Permission Explorer assets from the rendered permission partial', function (): void {
    $project = dirname(__DIR__, 4);
    $viewPath = $project . '/app/zoosper-admin/resources/views/admin/roles/permission-tree.php';

    expect(is_file($viewPath))->toBeTrue();
    $view = file_get_contents($viewPath);
    expect($view !== false)->toBeTrue();
    expect(str_contains($view, '/assets/admin/css/permission-explorer.css?v=6d'))->toBeTrue();
    expect(str_contains($view, '/assets/admin/js/permission-explorer.js?v=6d'))->toBeTrue();
});

it('discovers the existing role form without requiring new server markup', function (): void {
    $runtimePath = dirname(__DIR__, 2) . '/resources/assets/admin/js/permission-explorer.js';

    expect(is_file($runtimePath))->toBeTrue();
    $runtime = file_get_contents($runtimePath);
    expect($runtime !== false)->toBeTrue();
    expect(str_contains($runtime, "checkbox.closest('form')"))->toBeTrue();
    expect(str_contains($runtime, 'permission_ids[]'))->toBeTrue();
});
