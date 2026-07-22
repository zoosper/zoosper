<?php

declare(strict_types=1);

namespace App\zoospercore\tests\Unit\Admin;

test('sites domains implementation blueprint preserves all required crud routes', function () {
    $root = dirname(__DIR__, 5);
    $doc = (string) file_get_contents($root . '/docs/roadmap/phase-1.37v3-sites-domains-crud-implementation-blueprint.md');

    foreach ([
        '/admin/sites',
        '/admin/sites/create',
        '/admin/sites/edit',
        '/admin/site-domains',
        '/admin/site-domains/create',
        '/admin/site-domains/edit',
    ] as $route) {
        expect($doc)->toContain($route);
    }

    expect($doc)->toContain('site.manage');
    expect($doc)->toContain('1.37v.3');
});

test('sites domains implementation target inspection is source only', function () {
    $root = dirname(__DIR__, 5);
    $tool = (string) file_get_contents($root . '/tools/inspect-sites-domains-implementation-targets.php');

    expect($tool)->toContain('Generated source-only inspection');
    expect($tool)->toContain('No .env files, uploaded media, database rows, secrets or runtime cache contents are read.');
    expect($tool)->toContain('sites-domains-implementation-targets.txt');
});

test('sites domains blueprint audit protects docs and tooling', function () {
    $root = dirname(__DIR__, 5);
    $audit = (string) file_get_contents($root . '/tools/audit-sites-domains-implementation-blueprint.php');

    expect($audit)->toContain('implementation blueprint');
    expect($audit)->toContain('controller blueprint');
    expect($audit)->toContain('operations guide');
    expect($audit)->toContain('Result: ');
});
