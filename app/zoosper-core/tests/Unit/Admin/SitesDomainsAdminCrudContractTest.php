<?php

declare(strict_types=1);

namespace App\zoospercore\tests\Unit\Admin;

test('sites and site domains admin crud audit documents expected launch routes', function () {
    $root = dirname(__DIR__, 5);
    $audit = (string) file_get_contents($root . '/tools/audit-sites-domains-admin-crud.php');

    foreach ([
        '/admin/sites',
        '/admin/sites/create',
        '/admin/sites/edit',
        '/admin/site-domains',
        '/admin/site-domains/create',
        '/admin/site-domains/edit',
    ] as $path) {
        expect($audit)->toContain($path);
    }
});

test('sites and site domains admin crud plan keeps scope focused', function () {
    $root = dirname(__DIR__, 5);
    $plan = (string) file_get_contents($root . '/docs/roadmap/phase-1.37v-sites-and-site-domains-admin-crud.md');

    expect($plan)->toContain('Site');
    expect($plan)->toContain('Site Domain');
    expect($plan)->toContain('/admin/sites');
    expect($plan)->toContain('/admin/site-domains');
    expect($plan)->toContain('Non-goals');
});

test('sites domains inspection tool is source only and reminds not to commit generated output', function () {
    $root = dirname(__DIR__, 5);
    $tool = (string) file_get_contents($root . '/tools/inspect-sites-domains-admin-crud.php');

    expect($tool)->toContain('sites-domains-admin-crud-inspection.txt');
    expect($tool)->toContain('Do not commit');
    expect($tool)->not->toContain('.env');
    expect($tool)->not->toContain('uploaded media');
});
