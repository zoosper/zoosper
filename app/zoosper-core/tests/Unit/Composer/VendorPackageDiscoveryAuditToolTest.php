<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

test('vendor package discovery audit tool documents discovery categories and duplicate checks', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/audit-vendor-package-discovery.php');

    expect($source)->toContain('Zoosper vendor package discovery audit');
    expect($source)->toContain('app modules');
    expect($source)->toContain('packages');
    expect($source)->toContain('vendor');
    expect($source)->toContain('duplicate module names absent');
});

test('vendor package module contract documentation contains composer extra metadata', function () {
    $root = dirname(__DIR__, 5);
    $doc = (string) file_get_contents($root . '/docs/architecture/vendor-package-module-discovery.md');

    expect($doc)->toContain('"type": "zoosper-module"');
    expect($doc)->toContain('"module": "module.php"');
    expect($doc)->toContain('Acme\\\\MovieLibrary\\\\');
});
