<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$out = $root . '/sites-domains-admin-crud-bulk-inspection.txt';

$wanted = [
    'admin route configs' => ['app/zoosper-admin/config', 'packages/zoosper-admin/config'],
    'admin controllers' => ['app/zoosper-admin/src/Controller', 'packages/zoosper-admin/src/Controller'],
    'admin templates' => ['app/zoosper-admin/views', 'app/zoosper-admin/templates', 'packages/zoosper-admin/views', 'packages/zoosper-admin/templates'],
    'site module config' => ['app/zoosper-site/config', 'packages/zoosper-site/config'],
    'site module source' => ['app/zoosper-site/src', 'packages/zoosper-site/src'],
    'core route/menu tests' => ['app/zoosper-core/tests/Unit', 'packages/zoosper-core/tests/Unit'],
];

$buffer = "Zoosper sites/domains admin CRUD bulk inspection\n";
$buffer .= "================================================\n\n";
$buffer .= "Generated source-only inspection. Do not commit this output by default.\n";
$buffer .= "No .env files, uploaded media, database table data or secrets are read.\n\n";

foreach ($wanted as $section => $paths) {
    $buffer .= "===== " . $section . " =====\n";
    foreach ($paths as $relative) {
        $path = $root . '/' . $relative;
        if (!file_exists($path)) {
            $buffer .= "Missing: " . $relative . "\n";
            continue;
        }

        if (is_file($path)) {
            $buffer .= "--- " . $relative . " ---\n" . file_get_contents($path) . "\n\n";
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            if (!in_array(strtolower($file->getExtension()), ['php', 'latte', 'phtml', 'html', 'md'], true)) {
                continue;
            }
            $relativeFile = str_replace($root . '/', '', $file->getPathname());
            $buffer .= "--- " . $relativeFile . " ---\n" . file_get_contents($file->getPathname()) . "\n\n";
        }
    }
    $buffer .= "\n";
}

file_put_contents($out, $buffer);
print "Wrote sites-domains-admin-crud-bulk-inspection.txt\n";
print "Review it locally, then remove it before commit unless intentionally needed.\n";
