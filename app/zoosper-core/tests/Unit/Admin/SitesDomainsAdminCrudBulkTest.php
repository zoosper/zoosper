<?php

declare(strict_types=1);

namespace App\zoospercore\tests\Unit\Admin;

test('sites domains bulk audit captures all launch readiness crud routes', function () {
    $root = dirname(__DIR__, 5);
    $audit = (string) file_get_contents($root . '/tools/audit-sites-domains-admin-crud-bulk.php');

    foreach ([
        '/admin/sites',
        '/admin/sites/create',
        '/admin/sites/edit',
        '/admin/site-domains',
        '/admin/site-domains/create',
        '/admin/site-domains/edit',
    ] as $route) {
        expect($audit)->toContain($route);
    }
});

test('sites domains bulk inspection remains source only', function () {
    $root = dirname(__DIR__, 5);
    $tool = (string) file_get_contents($root . '/tools/inspect-sites-domains-admin-crud-bulk.php');

    expect($tool)->toContain('Generated source-only inspection');
    expect($tool)->toContain('No .env files, uploaded media, database table data or secrets are read.');
    expect($tool)->toContain('sites-domains-admin-crud-bulk-inspection.txt');
});

test('sites domains bulk roadmap defines implementation sequence', function () {
    $root = dirname(__DIR__, 5);
    $doc = (string) file_get_contents($root . '/docs/roadmap/phase-1.37v-bulk-sites-domains-admin-crud.md');

    expect($doc)->toContain('1.37v.1');
    expect($doc)->toContain('1.37v.2');
    expect($doc)->toContain('1.37v.3');
    expect($doc)->toContain('Sites admin CRUD');
    expect($doc)->toContain('Site Domains admin CRUD');
});
