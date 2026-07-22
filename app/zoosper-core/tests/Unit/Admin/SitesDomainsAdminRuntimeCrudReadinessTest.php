<?php

declare(strict_types=1);

namespace App\zoospercore\tests\Unit\Admin;

test('runtime crud audit tracks concrete implementation signals', function () {
    $root = dirname(__DIR__, 5);
    $audit = (string) file_get_contents($root . '/tools/audit-sites-domains-admin-crud-runtime.php');

    foreach (['/admin/sites', '/admin/sites/create', '/admin/sites/edit', '/admin/site-domains', '/admin/site-domains/create', '/admin/site-domains/edit'] as $route) {
        expect($audit)->toContain($route);
    }

    expect($audit)->toContain('Site admin controller class present');
    expect($audit)->toContain('Site domain admin controller class present');
    expect($audit)->toContain('NEEDS_IMPLEMENTATION');
});

test('temporary runtime crud preparer is intentionally not required', function () {
    $root = dirname(__DIR__, 5);

    expect(file_exists($root . '/tools/prepare-sites-domains-admin-crud-runtime.php'))->toBeFalse();
});

test('runtime crud operations document source inspection workflow and cleanup policy', function () {
    $root = dirname(__DIR__, 5);
    $ops = (string) file_get_contents($root . '/docs/operations/sites-and-site-domains-admin-crud.md');

    expect($ops)->toContain('tools/audit-sites-domains-admin-crud-runtime.php');
    expect($ops)->toContain('tools/inspect-sites-domains-implementation-targets.php');
    expect($ops)->toContain('rm -f sites-domains-implementation-targets.txt');
    expect($ops)->toContain('Do not commit temporary preparer tools');
    expect($ops)->toContain('tools/prepare-sites-domains-admin-crud-runtime.php');
    expect($ops)->toContain('should be absent before commit');
});
