<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$factoryPath = $basePath . '/app/zoosper-core/src/Bootstrap/ApplicationFactory.php';
$manifestPath = $basePath . '/config/service_providers.php';

print "Zoosper bootstrap provider manifest runtime wiring verification\n";
print "===============================================================\n\n";

$factory = is_file($factoryPath) ? (string) file_get_contents($factoryPath) : '';
$manifest = is_file($manifestPath) ? require $manifestPath : [];
$providers = is_array($manifest) && isset($manifest['providers']) && is_array($manifest['providers'])
    ? array_values(array_filter($manifest['providers'], 'is_string'))
    : [];

$loaderPosition = strpos($factory, 'ServiceProviderManifestLoader');
$controllerLoaderPosition = strpos($factory, 'ControllerProviderLoader');

$checks = [
    'ApplicationFactory exists' => is_file($factoryPath),
    'ServiceProviderManifestLoader exists' => class_exists(\Zoosper\Core\Bootstrap\ServiceProviderManifestLoader::class),
    'service provider manifest exists' => is_file($manifestPath),
    'manifest contains I18nServiceProvider' => in_array(\Zoosper\Core\I18n\I18nServiceProvider::class, $providers, true),
    'ApplicationFactory references ServiceProviderManifestLoader' => $loaderPosition !== false,
    'ApplicationFactory loads manifest into a container variable' => preg_match('/->load\s*\(\s*\$[A-Za-z_][A-Za-z0-9_]*/', $factory) === 1,
    'ApplicationFactory loads manifest before ControllerProviderLoader' => $loaderPosition !== false && $controllerLoaderPosition !== false && $loaderPosition < $controllerLoaderPosition,
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
