<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$out = $root . '/sites-domains-admin-current-source-inspection.txt';

$targets = [
    'admin config' => ['app/zoosper-admin/config', 'packages/zoosper-admin/config'],
    'admin controllers' => ['app/zoosper-admin/src/Controller', 'packages/zoosper-admin/src/Controller'],
    'admin views/templates' => ['app/zoosper-admin/views', 'app/zoosper-admin/templates', 'packages/zoosper-admin/views', 'packages/zoosper-admin/templates'],
    'site config' => ['app/zoosper-site/config', 'packages/zoosper-site/config'],
    'site source' => ['app/zoosper-site/src', 'packages/zoosper-site/src'],
    'route tests' => ['app/zoosper-core/tests/Unit/Routing', 'app/zoosper-core/tests/Unit/Admin'],
];

$buffer = "Zoosper current source inspection for Sites/Site Domains admin CRUD\n";
$buffer .= "================================================================\n\n";
$buffer .= "Generated source-only inspection. Do not commit this output by default.\n";
$buffer .= "No .env files, uploaded media, database rows or secrets are read.\n\n";

foreach ($targets as $section => $paths) {
    $buffer .= "===== " . $section . " =====\n";
    foreach ($paths as $relative) {
        $path = $root . '/' . $relative;
        if (!file_exists($path)) {
            $buffer .= "Missing: " . $relative . "\n";
            continue;
        }
        if (is_file($path)) {
            $buffer .= renderFile($root, $path);
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
            $buffer .= renderFile($root, $file->getPathname());
        }
    }
    $buffer .= "\n";
}

file_put_contents($out, $buffer);
print "Wrote sites-domains-admin-current-source-inspection.txt\n";
print "Review locally and remove before commit unless intentionally needed.\n";

function renderFile(string $root, string $path): string
{
    return "--- " . str_replace($root . '/', '', $path) . " ---\n" . file_get_contents($path) . "\n\n";
}
