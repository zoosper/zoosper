<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Documentation;

test('root package owned documentation policy exists', function () {
    $root = dirname(__DIR__, 5);
    $doc = (string) file_get_contents($root . '/docs/architecture/package-owned-documentation.md');

    expect($doc)->toContain('Package-owned documentation policy');
    expect($doc)->toContain('packages/<vendor-module>/docs/architecture/');
    expect($doc)->toContain('root `docs/` folder should become');
});

test('package docs migration tools are present and source only', function () {
    $root = dirname(__DIR__, 5);
    $audit = (string) file_get_contents($root . '/tools/audit-doc-package-ownership.php');
    $plan = (string) file_get_contents($root . '/tools/plan-package-docs-migration.php');

    expect($audit)->toContain('documentation package ownership audit');
    expect($plan)->toContain('PACKAGE DOCS MIGRATION PLAN');
    expect($plan)->not->toContain('.env');
});
