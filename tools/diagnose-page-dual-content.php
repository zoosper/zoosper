<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/page-content-schema-db.php';

$pdo = zoosper_phase077_pdo($basePath);
print "Zoosper page dual content diagnostics\n";
print "====================================\n\n";
print 'pages.content_format: ' . (zoosper_phase077_column_exists($pdo, 'pages', 'content_format') ? 'yes' : 'no') . PHP_EOL;
print 'pages.content_json  : ' . (zoosper_phase077_column_exists($pdo, 'pages', 'content_json') ? 'yes' : 'no') . PHP_EOL;

if (zoosper_phase077_column_exists($pdo, 'pages', 'content_format')) {
    $rows = $pdo->query("SELECT id, slug, content_format, content_json IS NOT NULL AS has_json FROM pages ORDER BY id ASC")->fetchAll();
    foreach ($rows as $row) {
        print '- page #' . $row['id'] . ' /' . $row['slug'] . ' format=' . $row['content_format'] . ' has_json=' . ((int) $row['has_json'] === 1 ? 'yes' : 'no') . PHP_EOL;
    }
}
