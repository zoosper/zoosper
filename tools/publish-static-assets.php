<?php

declare(strict_types=1);

/**
 * Publish theme static assets to public/static.
 *
 * Initial foundation command. Later this can move to:
 * php bin/zoosper static:publish
 */

$basePath = require __DIR__ . '/bootstrap.php';
$options = getopt('', ['theme::', 'all', 'dry-run']);
$theme = isset($options['theme']) ? trim((string) $options['theme']) : 'default';
$dryRun = array_key_exists('dry-run', $options);
$publishAll = array_key_exists('all', $options);

$themeRoots = [];
if ($publishAll) {
    foreach (glob($basePath . '/themes/*', GLOB_ONLYDIR) ?: [] as $themePath) {
        $themeRoots[basename($themePath)] = $themePath;
    }
} else {
    $themeRoots[$theme] = $basePath . '/themes/' . $theme;
}

$blockedExtensions = [
    'php', 'phtml', 'phar', 'sql', 'sh', 'bash', 'bat', 'cmd', 'env', 'ini', 'pem', 'key', 'crt', 'p12', 'pfx',
];

print "Zoosper static asset publisher\n";
print "==============================\n\n";
print 'Mode: ' . ($dryRun ? 'dry-run' : 'publish') . PHP_EOL;
print 'Themes: ' . implode(', ', array_keys($themeRoots)) . PHP_EOL . PHP_EOL;

$copied = 0;
$skipped = 0;
$failed = false;

foreach ($themeRoots as $themeCode => $themeRoot) {
    $sourceRoot = $themeRoot . '/assets';
    $targetRoot = $basePath . '/public/static/themes/' . $themeCode . '/assets';

    if (!is_dir($sourceRoot)) {
        print '- ' . $themeCode . ': no assets directory, skipped' . PHP_EOL;
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
    );

    foreach ($iterator as $item) {
        /** @var SplFileInfo $item */
        if ($item->isDir()) {
            continue;
        }

        $relativePath = ltrim(str_replace($sourceRoot, '', $item->getPathname()), DIRECTORY_SEPARATOR);
        if ($relativePath === '' || str_contains($relativePath, '..')) {
            $skipped++;
            continue;
        }

        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        if (in_array($extension, $blockedExtensions, true)) {
            print '- skipped blocked asset: ' . $themeCode . '/' . $relativePath . PHP_EOL;
            $skipped++;
            continue;
        }

        $target = $targetRoot . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
        print '- ' . ($dryRun ? 'would copy: ' : 'copy: ') . $themeCode . '/' . $relativePath . PHP_EOL;

        if ($dryRun) {
            continue;
        }

        $targetDir = dirname($target);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            print '  FAIL: unable to create ' . $targetDir . PHP_EOL;
            $failed = true;
            continue;
        }

        if (!copy($item->getPathname(), $target)) {
            print '  FAIL: unable to copy to ' . $target . PHP_EOL;
            $failed = true;
            continue;
        }

        $copied++;
    }
}

print "\nCopied: " . $copied . PHP_EOL;
print "Skipped: " . $skipped . PHP_EOL;
print "Result: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
