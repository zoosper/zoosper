<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/page-content-schema-db.php';

$pdo = zoosper_phase077_pdo($basePath);
print "Zoosper page content format data audit\n";
print "======================================\n\n";

if (!zoosper_phase077_column_exists($pdo, 'pages', 'content_format')) {
    print "pages.content_format is missing. Run apply-page-content-format-schema.php first.\nResult: REVIEW_REQUIRED\n";
    exit(1);
}

$rows = $pdo->query("SELECT content_format, COUNT(*) AS total FROM pages GROUP BY content_format ORDER BY content_format")->fetchAll();
foreach ($rows as $row) {
    print '- ' . ($row['content_format'] ?? 'NULL') . ': ' . $row['total'] . PHP_EOL;
}

$invalid = $pdo->query("SELECT COUNT(*) FROM pages WHERE content_format NOT IN ('html', 'block_json', 'markdown')")->fetchColumn();
print "\nInvalid formats: " . (int) $invalid . PHP_EOL;
print "Result: " . ((int) $invalid === 0 ? 'OK' : 'REVIEW_REQUIRED') . PHP_EOL;
exit((int) $invalid === 0 ? 0 : 1);
