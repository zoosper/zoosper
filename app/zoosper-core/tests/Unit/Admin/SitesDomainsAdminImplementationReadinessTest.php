<?php

declare(strict_types=1);

namespace App\zoospercore\tests\Unit\Admin;

test('sites domains implementation audit tracks concrete crud signals', function () {
    $root = dirname(__DIR__, 5);
    $audit = (string) file_get_contents($root . '/tools/audit-sites-domains-admin-crud-implementation.php');

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

    expect($audit)->toContain('site admin controller implementation exists');
    expect($audit)->toContain('site domains admin controller implementation exists');
    expect($audit)->toContain('NEEDS_IMPLEMENTATION');
});

test('current source inspection tool remains source only', function () {
    $root = dirname(__DIR__, 5);
    $tool = (string) file_get_contents($root . '/tools/inspect-sites-domains-admin-current-source.php');

    expect($tool)->toContain('Generated source-only inspection');
    expect($tool)->toContain('No .env files, uploaded media, database rows or secrets are read.');
    expect($tool)->toContain('sites-domains-admin-current-source-inspection.txt');
});

test('implementation readiness roadmap keeps launch readiness and deferred items visible', function () {
    $root = dirname(__DIR__, 5);
    $doc = (string) file_get_contents($root . '/docs/roadmap/phase-1.37v2-sites-domains-implementation-readiness.md');

    expect($doc)->toContain('1.37v.1');
    expect($doc)->toContain('1.37v.2');
    expect($doc)->toContain('1.37v.3');
    expect($doc)->toContain('deferred-near-term.md');
});
