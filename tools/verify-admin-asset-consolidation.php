<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$layout = is_file($basePath . '/themes/admin/default/templates/layout.php')
    ? (string) file_get_contents($basePath . '/themes/admin/default/templates/layout.php')
    : '';

print "Zoosper admin asset consolidation verification\n";
print "==============================================\n\n";

$checks = [
    'admin layout exists' => $layout !== '',
    'admin layout has no hard-coded /themes asset link' => !str_contains($layout, '/themes/admin/default/assets/css/admin.css'),
    'public admin css exists' => is_file($basePath . '/public/assets/admin/css/admin.css'),
    'public themes absent' => !file_exists($basePath . '/public/themes'),
    'public webroot blocks /themes/' => in_array('/themes/', (require $basePath . '/config/public_webroot.php')['blocked_roots'] ?? [], true),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
