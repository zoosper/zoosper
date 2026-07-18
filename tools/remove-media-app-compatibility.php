<?php

declare(strict_types=1);

/**
 * Phase 1.37h: remove the app/zoosper-media compatibility path after the media
 * module has been piloted as a Composer/path package.
 *
 * This tool is intentionally conservative. It removes only a symlink at
 * app/zoosper-media. If app/zoosper-media is a real directory, the tool refuses
 * to continue because that means the media module has not been moved safely yet.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$dryRun = in_array('--dry-run', $argv, true);
$compatPath = $basePath . '/app/zoosper-media';
$packagePath = $basePath . '/packages/zoosper-media';

print "Zoosper media app compatibility removal\n";
print "=======================================\n\n";
print 'Mode: ' . ($dryRun ? 'dry-run' : 'apply') . PHP_EOL;

if (!is_dir($packagePath) || !is_file($packagePath . '/module.php') || !is_file($packagePath . '/composer.json')) {
    fail('packages/zoosper-media is not ready. Apply and verify Phase 1.37f first.');
}

if (!is_link($compatPath)) {
    if (is_dir($compatPath)) {
        fail('app/zoosper-media is a real directory, not a compatibility symlink. Refusing to delete source.');
    }

    print "app/zoosper-media compatibility path is already absent.\n";
    print "Result: OK\n";
    exit(0);
}

$target = readlink($compatPath) ?: '';
print 'Compatibility symlink: app/zoosper-media -> ' . $target . PHP_EOL;

if ($dryRun) {
    print "\nDry-run only. No files changed.\n";
    exit(0);
}

if (!unlink($compatPath)) {
    fail('Unable to remove app/zoosper-media compatibility symlink.');
}

print "\nRemoved app/zoosper-media compatibility symlink.\n";
print "Next commands:\n";
print "  php8.5 tools/verify-media-package-independent-discovery.php\n";
print "  PHP=php8.5 composer dump-autoload\n";
print "  PHP=php8.5 bin/verify\n";

function fail(string $message): never
{
    fwrite(STDERR, "ERROR: {$message}\n");
    exit(1);
}
