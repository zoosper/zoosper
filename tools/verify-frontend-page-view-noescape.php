<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$phpPath = $basePath . '/themes/default/templates/modules/zoosper-page/page/view.php';
$lattePath = $basePath . '/themes/default/templates/modules/zoosper-page/page/view.latte';
$php = is_file($phpPath) ? (string) file_get_contents($phpPath) : '';
$latte = is_file($lattePath) ? (string) file_get_contents($lattePath) : '';

print "Zoosper frontend page view noescape verification\n";
print "================================================\n\n";

$checks = [
    'theme module PHP view exists' => $php !== '',
    'theme module PHP view renders page content directly' => str_contains($php, '<?= $page->content ?>'),
    'theme module PHP view does not escape page content' => !str_contains($php, '$e($page->content') && !str_contains($php, 'nl2br($e($page->content') && !str_contains($php, 'htmlspecialchars($page->content'),
    'theme module Latte view exists' => $latte !== '',
    'theme module Latte view renders page content noescape' => str_contains($latte, '{$page->content|noescape}'),
    'theme module Latte view does not render escaped page content placeholder' => !str_contains($latte, '{$page->content}') || str_contains($latte, '{$page->content|noescape}'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
