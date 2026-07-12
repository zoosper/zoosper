<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/page-content-schema-db.php';

$pdo = zoosper_phase077_pdo($basePath);
$schema = require $basePath . '/database/schema/page_seo_metadata.php';
$table = (string) $schema['table'];

print "Zoosper page SEO metadata schema apply\n";
print "======================================\n\n";

$definitions = [
    'meta_title' => "VARCHAR(255) NULL AFTER `content_json`",
    'meta_description' => "VARCHAR(500) NULL AFTER `meta_title`",
    'meta_keywords' => "VARCHAR(500) NULL AFTER `meta_description`",
    'canonical_url' => "VARCHAR(500) NULL AFTER `meta_keywords`",
];

$added = 0;
foreach ($definitions as $column => $definition) {
    if (zoosper_phase077_column_exists($pdo, $table, $column)) {
        print '- pages.' . $column . " already exists\n";
        continue;
    }

    $pdo->exec('ALTER TABLE `' . $table . '` ADD COLUMN `' . $column . '` ' . $definition);
    $added++;
    print '- added pages.' . $column . PHP_EOL;
}

print "\nColumns added: {$added}\nResult: OK\n";
