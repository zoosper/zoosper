<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$composerFile = $basePath . '/composer.json';
$composer = is_file($composerFile) ? json_decode((string) file_get_contents($composerFile), true) : [];
$actual = is_array($composer) ? ($composer['autoload']['psr-4'] ?? []) : [];
$actualDev = is_array($composer) ? ($composer['autoload-dev']['psr-4'] ?? []) : [];
$expected = (new \Zoosper\Core\Composer\ModuleAutoloadSynchronizer($basePath))->discoverMappings();

print "Zoosper module autoload sync verification\n";
print "==========================================\n\n";

$failed = false;
foreach ($expected['autoload'] as $namespace => $path) {
    $ok = ($actual[$namespace] ?? null) === $path;
    print '- ' . $namespace . ' => ' . $path . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}
foreach ($expected['autoload-dev'] as $namespace => $path) {
    $ok = ($actualDev[$namespace] ?? null) === $path;
    print '- ' . $namespace . ' => ' . $path . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
