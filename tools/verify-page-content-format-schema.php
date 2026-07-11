<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/page-content-schema-db.php';

print "Zoosper page content format schema verification\n";
print "================================================\n\n";

$pdo = zoosper_phase077_pdo($basePath);
$schema = require $basePath . '/database/schema/page_content_format.php';
$table = (string) $schema['table'];

$checks = [
    'schema file exists' => is_file($basePath . '/database/schema/page_content_format.php'),
    'ContentFormat enum exists' => enum_exists(\Zoosper\Page\Content\ContentFormat::class),
    'PageContentDocument exists' => class_exists(\Zoosper\Page\Content\PageContentDocument::class),
    'pages.content_format exists' => zoosper_phase077_column_exists($pdo, $table, 'content_format'),
    'pages.content_json exists' => zoosper_phase077_column_exists($pdo, $table, 'content_json'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
