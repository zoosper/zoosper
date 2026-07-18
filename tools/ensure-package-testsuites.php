<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$phpunitFile = $basePath . '/phpunit.xml';
$dryRun = in_array('--dry-run', $argv, true);

print "Zoosper package testsuite synchronisation\n";
print "=========================================\n\n";
print 'Mode: ' . ($dryRun ? 'dry-run' : 'apply') . PHP_EOL;

if (!is_file($phpunitFile)) {
    fwrite(STDERR, "ERROR: phpunit.xml not found.\n");
    exit(1);
}

$xml = (string) file_get_contents($phpunitFile);
if (str_contains($xml, 'packages/*/tests')) {
    print "packages/*/tests is already present in phpunit.xml.\n";
    print "Result: OK\n";
    exit(0);
}

$line = "        <directory>packages/*/tests</directory>\n";

$patterns = [
    '/(<testsuite\s+name="Unit"[^>]*>\s*)/m',
    '/(<testsuite[^>]*>\s*)/m',
];

$updated = null;
foreach ($patterns as $pattern) {
    if (preg_match($pattern, $xml, $matches, PREG_OFFSET_CAPTURE) === 1) {
        $position = $matches[1][1] + strlen($matches[1][0]);
        $updated = substr($xml, 0, $position) . $line . substr($xml, $position);
        break;
    }
}

if ($updated === null) {
    fwrite(STDERR, "ERROR: Unable to locate a testsuite in phpunit.xml. Add <directory>packages/*/tests</directory> manually.\n");
    exit(1);
}

print "Will add packages/*/tests to phpunit.xml.\n";

if ($dryRun) {
    print "\nDry-run only. No files changed.\n";
    exit(0);
}

file_put_contents($phpunitFile, $updated);

print "\nUpdated phpunit.xml.\n";
print "Next commands:\n";
print "  php8.5 tools/verify-package-testsuites.php\n";
print "  PHP=php8.5 composer dump-autoload\n";
print "  PHP=php8.5 bin/verify\n";
