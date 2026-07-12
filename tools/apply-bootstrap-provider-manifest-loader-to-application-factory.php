<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$factoryPath = $basePath . '/app/zoosper-core/src/Bootstrap/ApplicationFactory.php';

print "Zoosper bootstrap provider manifest runtime wiring\n";
print "==================================================\n\n";

if (!is_file($factoryPath)) {
    fwrite(STDERR, "Missing ApplicationFactory: {$factoryPath}\n");
    exit(2);
}

$source = file_get_contents($factoryPath);
if ($source === false) {
    fwrite(STDERR, "Unable to read ApplicationFactory.\n");
    exit(2);
}

if (str_contains($source, 'ServiceProviderManifestLoader') && str_contains($source, '->load($container')) {
    print "ApplicationFactory already loads config/service_providers.php.\n";
    exit(0);
}

if (!preg_match('/\$container\b/', $source)) {
    fwrite(STDERR, "Unable to find a $container variable in ApplicationFactory. Manual wiring is required.\n");
    exit(2);
}

$basePathExpression = 'dirname(__DIR__, 4)';
if (str_contains($source, '$this->basePath')) {
    $basePathExpression = '$this->basePath';
} elseif (preg_match('/\$basePath\b/', $source)) {
    $basePathExpression = '$basePath';
}

$snippet = "\n        // Phase 0.99: load root service providers declared in config/service_providers.php.\n"
    . "        (new \\Zoosper\\Core\\Bootstrap\\ServiceProviderManifestLoader({$basePathExpression}))->load(\$container);\n";

$updated = null;
if (preg_match('/\n\s*return\s+\$container\s*;/', $source, $match, PREG_OFFSET_CAPTURE)) {
    $position = $match[0][1];
    $updated = substr($source, 0, $position) . $snippet . substr($source, $position);
} elseif (preg_match('/\n\s*return\s+new\s+[A-Za-z0-9_\\\\]+\s*\(/', $source, $match, PREG_OFFSET_CAPTURE)) {
    $position = $match[0][1];
    $updated = substr($source, 0, $position) . $snippet . substr($source, $position);
}

if ($updated === null) {
    fwrite(STDERR, "Unable to find a safe insertion point in ApplicationFactory. Manual wiring is required.\n");
    exit(2);
}

$backupPath = $factoryPath . '.phase-0.99.bak';
if (!is_file($backupPath)) {
    copy($factoryPath, $backupPath);
    print "Backup: app/zoosper-core/src/Bootstrap/ApplicationFactory.php.phase-0.99.bak\n";
}

file_put_contents($factoryPath, $updated);
print "Updated: app/zoosper-core/src/Bootstrap/ApplicationFactory.php\n";
print "Manifest loader call inserted before the container/application return path.\n";
