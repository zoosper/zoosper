<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$out = $root . '/sites-domains-implementation-targets.txt';

$targets = [
    'admin controller conventions' => [
        'app/zoosper-admin/src/Controller',
        'packages/zoosper-admin/src/Controller',
    ],
    'admin route registration' => [
        'app/zoosper-admin/config/routes.php',
        'app/zoosper-admin/config/admin_routes.php',
        'packages/zoosper-admin/config/routes.php',
    ],
    'admin service/controller factories' => [
        'app/zoosper-admin/config/services.php',
        'app/zoosper-admin/config/controllers.php',
        'packages/zoosper-admin/config/services.php',
        'packages/zoosper-admin/config/controllers.php',
    ],
    'admin menu/sidebar source' => [
        'app/zoosper-admin/config/admin_menu.php',
        'app/zoosper-admin/src',
        'packages/zoosper-admin/config/admin_menu.php',
    ],
    'site module schema and repositories' => [
        'app/zoosper-site/config/db_schema.php',
        'app/zoosper-site/src',
        'packages/zoosper-site/config/db_schema.php',
        'packages/zoosper-site/src',
    ],
];

$buffer = "Zoosper Sites/Site Domains implementation target inspection\n";
$buffer .= "==========================================================\n\n";
$buffer .= "Generated source-only inspection. Do not commit this output by default.\n";
$buffer .= "No .env files, uploaded media, database rows, secrets or runtime cache contents are read.\n\n";

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
print "Wrote sites-domains-implementation-targets.txt\n";
print "Review locally, then remove before commit unless intentionally needed.\n";

function renderFile(string $root, string $path): string
{
    return "--- " . str_replace($root . '/', '', $path) . " ---\n" . file_get_contents($path) . "\n\n";
}
