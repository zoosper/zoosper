<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin form config empty handle verification\n";
print "==================================================\n\n";

$config = (new \Zoosper\Admin\Form\AdminFormConfigAggregator($basePath))->aggregate();

$checks = [
    'aggregated config has processors key' => isset($config['processors']) && is_array($config['processors']),
    'aggregated config preserves page.form processor handle' => array_key_exists('page.form', $config['processors'] ?? []),
    'page.form processors value is an array' => isset($config['processors']['page.form']) && is_array($config['processors']['page.form']),
    'empty page.form processor list is allowed' => array_key_exists('page.form', $config['processors'] ?? []) && count($config['processors']['page.form']) === 0,
    'aggregated config still has forms key' => isset($config['forms']) && is_array($config['forms']),
    'aggregated config still has page.form sections' => isset($config['forms']['page.form']) && count($config['forms']['page.form']) >= 4,
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
