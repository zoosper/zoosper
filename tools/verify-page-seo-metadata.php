<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/page-content-schema-db.php';

$pdo = zoosper_phase077_pdo($basePath);
$model = (string) file_get_contents($basePath . '/app/zoosper-page/src/Model/Page.php');
$repository = (string) file_get_contents($basePath . '/app/zoosper-page/src/Repository/PageRepository.php');
$controller = (string) file_get_contents($basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php');

print "Zoosper page SEO metadata verification\n";
print "======================================\n\n";

$checks = [
    'schema file exists' => is_file($basePath . '/database/schema/page_seo_metadata.php'),
    'Page model has metaTitle' => str_contains($model, 'metaTitle'),
    'Page model has metaDescription' => str_contains($model, 'metaDescription'),
    'Page model has metaKeywords' => str_contains($model, 'metaKeywords'),
    'Page model has canonicalUrl' => str_contains($model, 'canonicalUrl'),
    'Repository references meta_title' => str_contains($repository, 'meta_title'),
    'Repository references meta_description' => str_contains($repository, 'meta_description'),
    'Repository references meta_keywords' => str_contains($repository, 'meta_keywords'),
    'Repository references canonical_url' => str_contains($repository, 'canonical_url'),
    'Admin controller has SEO section' => str_contains($controller, 'Search engine optimisation'),
    'Admin controller accepts meta title' => str_contains($controller, "form['meta_title']"),
    'pages.meta_title exists' => zoosper_phase077_column_exists($pdo, 'pages', 'meta_title'),
    'pages.meta_description exists' => zoosper_phase077_column_exists($pdo, 'pages', 'meta_description'),
    'pages.meta_keywords exists' => zoosper_phase077_column_exists($pdo, 'pages', 'meta_keywords'),
    'pages.canonical_url exists' => zoosper_phase077_column_exists($pdo, 'pages', 'canonical_url'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
