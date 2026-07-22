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

test('runtime crud preparer is source inspection gated', function () {
    $root = dirname(__DIR__, 5);
    $tool = (string) file_get_contents($root . '/tools/prepare-sites-domains-admin-crud-runtime.php');

    expect($tool)->toContain('sites-domains-implementation-targets.txt');
    expect($tool)->toContain('PageAdminController convention visible');
    expect($tool)->toContain('No write performed');
    expect($tool)->toContain('source-specific patch');
});
