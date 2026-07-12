<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$manifestPath = $basePath . '/config/service_providers.php';
$providerClass = \Zoosper\Core\I18n\I18nServiceProvider::class;

print "Zoosper service provider manifest file verification\n";
print "===================================================\n\n";

$manifest = is_file($manifestPath) ? require $manifestPath : [];
$providers = [];
if (is_array($manifest)) {
    $providers = isset($manifest['providers']) && is_array($manifest['providers'])
        ? array_values(array_filter($manifest['providers'], 'is_string'))
        : array_values(array_filter($manifest, 'is_string'));
}

$checks = [
    'config/service_providers.php exists' => is_file($manifestPath),
    'config/service_providers.php returns array' => is_array($manifest),
    'manifest has providers list' => isset($manifest['providers']) && is_array($manifest['providers']),
    'I18nServiceProvider class exists' => class_exists($providerClass),
    'manifest includes I18nServiceProvider' => in_array($providerClass, $providers, true),
    'manifest includes I18nServiceProvider exactly once' => count(array_keys($providers, $providerClass, true)) === 1,
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
