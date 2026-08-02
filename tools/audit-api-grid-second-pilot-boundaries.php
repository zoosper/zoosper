<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$genericRoots = [
    $root . '/packages/zoosper-grid',
    $root . '/packages/zoosper-admin-grid',
    $root . '/packages/zoosper-api-grid',
];
$featureTokens = ['StoreOrder', 'store_order', 'store-order', 'store orders'];
$violations = [];
$scannedRoots = [];

foreach ($genericRoots as $genericRoot) {
    if (!is_dir($genericRoot)) {
        continue;
    }
    $scannedRoots[] = substr($genericRoot, strlen($root) + 1);
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($genericRoot, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if (!$file->isFile() || !in_array($file->getExtension(), ['php', 'md', 'json'], true)) {
            continue;
        }
        $relative = substr($file->getPathname(), strlen($root) + 1);
        if (str_contains($relative, '/vendor/')) {
            continue;
        }
        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            continue;
        }
        foreach ($featureTokens as $token) {
            if (stripos($content, $token) !== false) {
                $violations[$relative][] = $token;
            }
        }
    }
}

if ($scannedRoots === []) {
    fwrite(STDERR, "ERROR: no generic API Grid package roots were found under {$root}/packages.\n");
    exit(1);
}

ksort($violations);
$report = [
    '# API Grid second-pilot boundary audit',
    '',
    'Repository root: `' . $root . '`',
    'Scanned roots: `' . implode('`, `', $scannedRoots) . '`',
    '',
    'This report checks generic Grid packages for Store Orders-specific tokens before a second pilot is selected.',
    '',
];
if ($violations === []) {
    $report[] = 'Result: no Store Orders-specific tokens were found in the generic package roots.';
} else {
    $report[] = 'Result: review the following possible feature leaks before implementing the second pilot:';
    $report[] = '';
    foreach ($violations as $file => $tokens) {
        $report[] = '- `' . $file . '`: ' . implode(', ', array_unique($tokens));
    }
}
$report[] = '';
$report[] = 'This is a lexical audit, not proof of architectural correctness. Each match requires human review.';

$target = $root . '/var/reports/api-grid-second-pilot-boundary-audit.md';
if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0775, true) && !is_dir(dirname($target))) {
    throw new RuntimeException('Unable to create report directory.');
}
if (file_put_contents($target, implode("\n", $report) . "\n") === false) {
    throw new RuntimeException('Unable to write API Grid boundary report.');
}
echo $target . "\n";
