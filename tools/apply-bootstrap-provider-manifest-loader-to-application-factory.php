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

if (str_contains($source, 'ServiceProviderManifestLoader') && preg_match('/->load\s*\(\s*\$[A-Za-z_][A-Za-z0-9_]*/', $source) === 1) {
    print "ApplicationFactory already loads config/service_providers.php.\n";
    exit(0);
}

$containerVariable = detect_container_variable($source);
if ($containerVariable === null) {
    fwrite(STDERR, "Unable to identify the service container variable in ApplicationFactory.\n");
    fwrite(STDERR, "Looked for assignments to ServiceContainer and existing service-provider loader calls.\n");
    exit(2);
}

$basePathExpression = detect_base_path_expression($source);
$snippet = "\n        // Phase 0.99.1: load root service providers declared in config/service_providers.php.\n"
    . "        (new \\Zoosper\\Core\\Bootstrap\\ServiceProviderManifestLoader({$basePathExpression}))->load({$containerVariable});\n";

$updated = insert_before_return($source, $snippet);
if ($updated === null) {
    fwrite(STDERR, "Unable to find a safe insertion point in ApplicationFactory.\n");
    exit(2);
}

$backupPath = $factoryPath . '.phase-0.99.1.bak';
if (!is_file($backupPath)) {
    copy($factoryPath, $backupPath);
    print "Backup: app/zoosper-core/src/Bootstrap/ApplicationFactory.php.phase-0.99.1.bak\n";
}

file_put_contents($factoryPath, $updated);
print "Updated: app/zoosper-core/src/Bootstrap/ApplicationFactory.php\n";
print "Container variable: {$containerVariable}\n";
print "Manifest loader call inserted before the return path.\n";

function detect_container_variable(string $source): ?string
{
    $patterns = [
        '/\$(?<name>[A-Za-z_][A-Za-z0-9_]*)\s*=\s*new\s+\\?Zoosper\\Core\\Container\\ServiceContainer\s*\(/',
        '/\$(?<name>[A-Za-z_][A-Za-z0-9_]*)\s*=\s*new\s+ServiceContainer\s*\(/',
        '/ServiceProviderLoader::[^;]*\(\s*\$(?<name>[A-Za-z_][A-Za-z0-9_]*)\s*[,\)]/',
        '/->register\s*\(\s*\$(?<name>[A-Za-z_][A-Za-z0-9_]*)\s*\)/',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source, $matches) === 1) {
            return '$' . $matches['name'];
        }
    }

    foreach (['$container', '$serviceContainer', '$services'] as $candidate) {
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

function insert_before_return(string $source, string $snippet): ?string
{
    $patterns = [
        '/\n\s*return\s+\$[A-Za-z_][A-Za-z0-9_]*\s*;/',
        '/\n\s*return\s+new\s+[A-Za-z0-9_\\\\]+\s*\(/',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source, $match, PREG_OFFSET_CAPTURE) === 1) {
            $position = $match[0][1];

            return substr($source, 0, $position) . $snippet . substr($source, $position);
        }
    }

    return null;
}
