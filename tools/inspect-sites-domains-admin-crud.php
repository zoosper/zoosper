<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$out = $root . '/sites-domains-admin-crud-inspection.txt';

$targets = [
    'admin services' => $root . '/app/zoosper-admin/config/services.php',
    'admin routes' => $root . '/app/zoosper-admin/config/routes.php',
    'admin menu' => $root . '/app/zoosper-admin/config/admin_menu.php',
    'site services' => $root . '/app/zoosper-site/config/services.php',
    'site routes' => $root . '/app/zoosper-site/config/routes.php',
    'site db schema' => $root . '/app/zoosper-site/config/db_schema.php',
    'site repository candidates' => $root . '/app/zoosper-site/src',
];

$buffer = "Zoosper sites and site domains admin CRUD inspection\n";
$buffer .= "===================================================\n\n";
$buffer .= "This file is generated for local implementation planning. Do not commit it unless explicitly needed.\n\n";

foreach ($targets as $label => $path) {
    $buffer .= "===== " . $label . " =====\n";
    if (is_file($path)) {
        $buffer .= file_get_contents($path) . "\n\n";
        continue;
    }
    if (is_dir($path)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }
            $buffer .= "--- " . str_replace($root . '/', '', $file->getPathname()) . " ---\n";
            $buffer .= file_get_contents($file->getPathname()) . "\n\n";
        }
        continue;
    }
    $buffer .= "Missing: " . str_replace($root . '/', '', $path) . "\n\n";
}

file_put_contents($out, $buffer);
print "Wrote sites-domains-admin-crud-inspection.txt\n";
print "Review it, then remove before commit unless intentionally used.\n";
