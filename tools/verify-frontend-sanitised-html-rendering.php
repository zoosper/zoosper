<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$phpLayoutPath = $basePath . '/themes/default/templates/layout.php';
$latteLayoutPath = $basePath . '/themes/default/templates/layout.latte';
$phpLayout = is_file($phpLayoutPath) ? (string) file_get_contents($phpLayoutPath) : '';
$latteLayout = is_file($latteLayoutPath) ? (string) file_get_contents($latteLayoutPath) : '';

print "Zoosper frontend sanitised HTML rendering verification\n";
print "======================================================\n\n";

$checks = [
    'PHP frontend layout exists' => $phpLayout !== '',
    'PHP layout renders content without e()' => str_contains($phpLayout, '<?= $content ??') || str_contains($phpLayout, '<?= $content'),
    'PHP layout does not escape content' => !str_contains($phpLayout, '$e($content') && !str_contains($phpLayout, 'htmlspecialchars($content'),
    'Latte frontend layout exists' => $latteLayout !== '',
    'Latte layout renders content noescape' => str_contains($latteLayout, '{$content|noescape}'),
    'Latte layout does not render escaped content placeholder' => !str_contains($latteLayout, '{$content}') || str_contains($latteLayout, '{$content|noescape}'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
