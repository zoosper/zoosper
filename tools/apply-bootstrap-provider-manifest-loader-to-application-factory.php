<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$factoryPath = $basePath . '/app/zoosper-core/src/Bootstrap/ApplicationFactory.php';

print "Zoosper bootstrap provider manifest ordering fix\n";
print "================================================\n\n";

if (!is_file($factoryPath)) {
    fwrite(STDERR, "Missing ApplicationFactory: {$factoryPath}\n");
    exit(2);
}

$source = file_get_contents($factoryPath);
if ($source === false) {
    fwrite(STDERR, "Unable to read ApplicationFactory.\n");
    exit(2);
}

$containerVariable = detect_container_variable($source);
if ($containerVariable === null) {
    fwrite(STDERR, "Unable to identify the service container variable in ApplicationFactory.\n");
    exit(2);
}

$basePathExpression = detect_base_path_expression($source);
$loaderCall = "        // Phase 1.00: load root service providers before controller providers are created.\n"
    . "        (new \\Zoosper\\Core\\Bootstrap\\ServiceProviderManifestLoader({$basePathExpression}))->load({$containerVariable});\n\n";

$source = remove_existing_manifest_loader_calls($source);
$needle = "        (new ServiceProviderLoader(\$modules, {$containerVariable}))->register();\n";
if (!str_contains($source, $needle)) {
    $needle = "        (new ServiceProviderLoader(\$modules, \$services))->register();\n";
}

if (!str_contains($source, $needle)) {
    fwrite(STDERR, "Unable to find ServiceProviderLoader registration insertion point.\n");
    exit(2);
}

$updated = str_replace($needle, $needle . $loaderCall, $source);
$backupPath = $factoryPath . '.phase-1.00.bak';
if (!is_file($backupPath)) {
    copy($factoryPath, $backupPath);
    print "Backup: app/zoosper-core/src/Bootstrap/ApplicationFactory.php.phase-1.00.bak\n";
}

file_put_contents($factoryPath, $updated);
print "Updated: app/zoosper-core/src/Bootstrap/ApplicationFactory.php\n";
print "Manifest loader now runs before ControllerProviderLoader.\n";

function detect_container_variable(string $source): ?string
{
    if (preg_match('/\$(?<name>[A-Za-z_][A-Za-z0-9_]*)\s*=\s*new\s+ServiceContainer\s*\(/', $source, $matches) === 1) {
        return '$' . $matches['name'];
    }

    foreach (['$services', '$container', '$serviceContainer'] as $candidate) {
        if (str_contains($source, $candidate)) {
            return $candidate;
        }
    }

    return null;
}

function detect_base_path_expression(string $source): string
{
    if (str_contains($source, '$this->basePath')) {
        return '$this->basePath';
    }

    if (preg_match('/\$basePath\b/', $source) === 1) {
        return '$basePath';
    }

    return 'dirname(__DIR__, 4)';
}

function remove_existing_manifest_loader_calls(string $source): string
{
    $source = preg_replace('/\n\s*\/\/ Phase 0\.99(?:\.1)?: load root service providers declared in config\/service_providers\.php\.\n\s*\(new \\Zoosper\\Core\\Bootstrap\\ServiceProviderManifestLoader\([^\n]+\n/', "\n", $source) ?? $source;
    $source = preg_replace('/\n\s*\/\/ Phase 1\.00: load root service providers before controller providers are created\.\n\s*\(new \\Zoosper\\Core\\Bootstrap\\ServiceProviderManifestLoader\([^\n]+\n\n?/', "\n", $source) ?? $source;

    return $source;
}
