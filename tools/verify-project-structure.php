<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$config = is_file($basePath . '/config/project_structure.php') ? require $basePath . '/config/project_structure.php' : [];

print "Zoosper project structure verification\n";
print "======================================\n\n";

$failed = false;
foreach (($config['required_roots'] ?? []) as $root) {
    $ok = is_dir($basePath . '/' . $root);
    print '- root ' . $root . ': ' . ($ok ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$ok;
}

foreach (($config['forbidden_public_roots'] ?? []) as $root) {
    $path = $basePath . '/public/' . $root;
    $ok = !file_exists($path);
    print '- public/' . $root . ' forbidden: ' . ($ok ? 'ok' : 'FOUND') . PHP_EOL;
    $failed = $failed || !$ok;
}

$nodeModulesPublic = is_dir($basePath . '/public/node_modules');
print '- public/node_modules absent: ' . (!$nodeModulesPublic ? 'ok' : 'FOUND') . PHP_EOL;
$failed = $failed || $nodeModulesPublic;

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
