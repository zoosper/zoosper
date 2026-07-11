<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/page-content-schema-db.php';

$pdo = zoosper_phase077_pdo($basePath);
$schema = require $basePath . '/database/schema/page_content_format.php';
$table = (string) $schema['table'];

print "Zoosper page content format schema apply\n";
print "========================================\n\n";

$added = 0;
if (!zoosper_phase077_column_exists($pdo, $table, 'content_format')) {
    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `content_format` VARCHAR(32) NOT NULL DEFAULT 'html' AFTER `content`");
    $added++;
    print "- added pages.content_format\n";
} else {
    print "- pages.content_format already exists\n";
}

if (!zoosper_phase077_column_exists($pdo, $table, 'content_json')) {
    $after = zoosper_phase077_column_exists($pdo, $table, 'content_format') ? 'content_format' : 'content';
    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `content_json` LONGTEXT NULL AFTER `{$after}`");
    $added++;
    print "- added pages.content_json\n";
} else {
    print "- pages.content_json already exists\n";
}

$pdo->exec("UPDATE `{$table}` SET `content_format` = 'html' WHERE `content_format` IS NULL OR `content_format` = ''");

print "\nColumns added: {$added}\nResult: OK\n";
