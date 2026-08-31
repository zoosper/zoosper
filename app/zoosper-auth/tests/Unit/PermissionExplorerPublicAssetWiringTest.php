<?php

declare(strict_types=1);

it('renders Permission Explorer through published public assets', function (): void {
    $project = dirname(__DIR__, 4);
    $viewPath = $project . '/app/zoosper-admin/resources/views/admin/roles/permission-tree.php';

    expect(is_file($viewPath))->toBeTrue();
    $view = file_get_contents($viewPath);
    expect($view !== false)->toBeTrue();
    expect(str_contains($view, '/assets/admin/css/permission-explorer.css?v=fcb99090ad65'))->toBeTrue();
    expect(str_contains($view, '/assets/admin/js/permission-explorer.js?v=9beeafa105ce'))->toBeTrue();
    expect(str_contains($view, '/asset/zoosper-auth/css/permission-explorer.css'))->toBeFalse();
    expect(str_contains($view, '/asset/zoosper-auth/js/permission-explorer.js'))->toBeFalse();
    expect(is_file($project . '/public/assets/admin/css/permission-explorer.css'))->toBeTrue();
    expect(is_file($project . '/public/assets/admin/js/permission-explorer.js'))->toBeTrue();
});










