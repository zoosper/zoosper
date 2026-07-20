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

test('package documentation ownership audit tool is present and source only', function () {
    $root = dirname(__DIR__, 5);
    $audit = (string) file_get_contents($root . '/tools/audit-doc-package-ownership.php');

    expect($audit)->toContain('documentation package ownership audit');
    expect($audit)->toContain('package docs directory exists');
    expect($audit)->toContain('Media docs still present under root docs');
});

test('package documentation migration planner is intentionally treated as a removable helper', function () {
    $root = dirname(__DIR__, 5);
    $doc = (string) file_get_contents($root . '/docs/operations/package-docs-migration.md');

    expect($doc)->toContain('php8.5 tools/audit-doc-package-ownership.php');
    expect($doc)->toContain('Do not keep generated migration plans in the repo');
    expect($doc)->toContain('rm -f tools/plan-package-docs-migration.php package-docs-migration-plan.txt');
});
