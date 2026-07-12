<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/page-content-schema-db.php';

$pdo = zoosper_phase077_pdo($basePath);
$columns = ['meta_title', 'meta_description', 'meta_keywords', 'canonical_url'];

print "Zoosper page SEO metadata diagnostics\n";
print "====================================\n\n";

foreach ($columns as $column) {
    print 'pages.' . $column . ': ' . (zoosper_phase077_column_exists($pdo, 'pages', $column) ? 'yes' : 'no') . PHP_EOL;
}

if (zoosper_phase077_column_exists($pdo, 'pages', 'meta_title')) {
    $rows = $pdo->query('SELECT id, slug, meta_title, canonical_url FROM pages ORDER BY id ASC')->fetchAll();
    foreach ($rows as $row) {
        print '- page #' . $row['id'] . ' /' . $row['slug'] . ' meta_title=' . (($row['meta_title'] ?? '') !== '' ? 'set' : 'empty') . ' canonical=' . (($row['canonical_url'] ?? '') !== '' ? 'set' : 'empty') . PHP_EOL;
    }
}
