<?php

declare(strict_types=1);

$root = dirname(__DIR__);

print "Zoosper Sites/Site Domains implementation blueprint audit\n";
print "=========================================================\n\n";

$files = [
    'implementation blueprint' => $root . '/docs/roadmap/phase-1.37v3-sites-domains-crud-implementation-blueprint.md',
    'controller blueprint' => $root . '/docs/architecture/sites-and-site-domains-controller-blueprint.md',
    'operations guide' => $root . '/docs/operations/sites-domains-implementation-targets.md',
    'inspection tool' => $root . '/tools/inspect-sites-domains-implementation-targets.php',
];

$failed = false;
foreach ($files as $label => $path) {
    $ok = is_file($path);
    print '- ' . $label . ': ' . ($ok ? 'ok' : 'MISSING') . PHP_EOL;
    $failed = $failed || !$ok;
}

$blueprint = is_file($files['implementation blueprint']) ? (string) file_get_contents($files['implementation blueprint']) : '';
foreach (['/admin/sites', '/admin/sites/create', '/admin/sites/edit', '/admin/site-domains', '/admin/site-domains/create', '/admin/site-domains/edit', 'site.manage'] as $needle) {
    $ok = str_contains($blueprint, $needle);
    print '- blueprint contains ' . $needle . ': ' . ($ok ? 'ok' : 'MISSING') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
