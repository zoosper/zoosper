<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$manifestPath = $basePath . '/config/service_providers.php';
$providerClass = \Zoosper\Core\I18n\I18nServiceProvider::class;

print "Zoosper i18n service provider discovery registration\n";
print "=====================================================\n\n";

if (!class_exists($providerClass)) {
    fwrite(STDERR, "Missing provider class: {$providerClass}\n");
    exit(2);
}

$manifest = ['providers' => []];
if (is_file($manifestPath)) {
    $loaded = require $manifestPath;
    if (!is_array($loaded)) {
        fwrite(STDERR, "Existing config/service_providers.php must return an array.\n");
        exit(2);
    }

    $manifest = normalise_manifest($loaded);
}

$providers = $manifest['providers'];
if (!in_array($providerClass, $providers, true)) {
    $providers[] = $providerClass;
}

$manifest['providers'] = array_values(array_unique($providers));

if (is_file($manifestPath)) {
    $backupPath = $manifestPath . '.phase-0.97.bak';
    if (!is_file($backupPath)) {
        copy($manifestPath, $backupPath);
        print "Backup: config/service_providers.php.phase-0.97.bak\n";
    }
}

$directory = dirname($manifestPath);
if (!is_dir($directory)) {
    mkdir($directory, 0775, true);
}

file_put_contents($manifestPath, render_manifest($manifest));
print "Updated: config/service_providers.php\n";
print "Registered provider: {$providerClass}\n";

/** @param array<mixed> $loaded */
function normalise_manifest(array $loaded): array
{
    if (isset($loaded['providers']) && is_array($loaded['providers'])) {
        return ['providers' => array_values(array_filter($loaded['providers'], 'is_string'))];
    }

    return ['providers' => array_values(array_filter($loaded, 'is_string'))];
}

/** @param array{providers: list<string>} $manifest */
function render_manifest(array $manifest): string
{
    $lines = [];
    $lines[] = '<?php';
    $lines[] = '';
    $lines[] = 'declare(strict_types=1);';
    $lines[] = '';
    $lines[] = 'return [';
    $lines[] = "    'providers' => [";
    foreach ($manifest['providers'] as $provider) {
        $lines[] = '        ' . '\\' . ltrim($provider, '\\') . '::class,';
    }
    $lines[] = '    ],';
    $lines[] = '];';

    return implode(PHP_EOL, $lines) . PHP_EOL;
}
