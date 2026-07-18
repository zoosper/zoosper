<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper module composer manifest verification\n";
print "=============================================\n\n";

$failed = false;
foreach (glob($basePath . '/app/*/module.php') ?: [] as $moduleFile) {
    $moduleDir = dirname($moduleFile);
    if (!is_dir($moduleDir . '/src')) {
        continue;
    }

    $module = require $moduleFile;
    if (!is_array($module) || ($module['enabled'] ?? true) === false) {
        continue;
    }

    $identity = \Zoosper\Core\Composer\ModulePackageIdentity::fromModule($module, basename($moduleDir));
    if ($identity === null) {
        print '- ' . basename($moduleDir) . ': FAIL invalid module identity' . PHP_EOL;
        $failed = true;
        continue;
    }

    $composerFile = $moduleDir . '/composer.json';
    $json = is_file($composerFile) ? json_decode((string) file_get_contents($composerFile), true) : null;
    $ok = is_array($json)
        && ($json['name'] ?? null) === $identity->packageName
        && ($json['type'] ?? null) === 'zoosper-module'
        && (($json['autoload']['psr-4'][$identity->namespace] ?? null) === 'src/');

    print '- ' . basename($moduleDir) . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
