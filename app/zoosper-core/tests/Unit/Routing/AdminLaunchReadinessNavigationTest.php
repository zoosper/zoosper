<?php

declare(strict_types=1);

namespace App\zoospercore\tests\Unit\Routing;

test('launch readiness navigation audit defines concrete admin targets', function () {
    $root = dirname(__DIR__, 5);
    $audit = (string) file_get_contents($root . '/tools/audit-admin-launch-readiness-navigation.php');

    foreach (['/admin/sites', '/admin/site-domains', '/admin/settings'] as $path) {
        expect($audit)->toContain($path);
    }

    expect($audit)->toContain('href="#"');
    expect($audit)->toContain('Site Domains');
    expect($audit)->toContain('Sites');
    expect($audit)->toContain('Settings');
    expect($audit)->toContain('Result: ');
});

test('temporary launch readiness helper tools are intentionally not required', function () {
    $root = dirname(__DIR__, 5);

    expect(file_exists($root . '/tools/apply-admin-launch-readiness-navigation.php'))->toBeFalse();
    expect(file_exists($root . '/tools/scaffold-admin-launch-readiness-stubs.php'))->toBeFalse();
});

test('launch readiness documentation stubs can be committed without temporary scaffolder', function () {
    $root = dirname(__DIR__, 5);
    $stubRoot = $root . '/app/zoosper-admin/docs/launch-readiness-stubs';

    foreach (['sites.md', 'site-domains.md', 'settings.md'] as $file) {
        expect(file_exists($stubRoot . '/' . $file))->toBeTrue();
    }
});
