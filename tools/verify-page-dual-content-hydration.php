<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/page-content-schema-db.php';

print "Zoosper page dual content hydration verification\n";
print "================================================\n\n";

$pdo = zoosper_phase077_pdo($basePath);
$model = is_file($basePath . '/app/zoosper-page/src/Model/Page.php') ? (string) file_get_contents($basePath . '/app/zoosper-page/src/Model/Page.php') : '';
$repository = is_file($basePath . '/app/zoosper-page/src/Repository/PageRepository.php') ? (string) file_get_contents($basePath . '/app/zoosper-page/src/Repository/PageRepository.php') : '';
$document = \Zoosper\Page\Content\PageContentDocument::fromRow([
    'content' => '<p>Hello</p>',
    'content_format' => 'html',
    'content_json' => null,
]);

$checks = [
    'Page model has contentFormat' => str_contains($model, 'contentFormat'),
    'Page model has contentJson' => str_contains($model, 'contentJson'),
    'Page model has hasBlockJson helper' => str_contains($model, 'hasBlockJson'),
    'PageRepository selects content_format' => str_contains($repository, 'content_format'),
    'PageRepository selects content_json' => str_contains($repository, 'content_json'),
    'PageRepository fallback format html' => str_contains($repository, "?? 'html'"),
    'PageContentDocument row defaults work' => $document->format->value === 'html' && $document->html === '<p>Hello</p>' && $document->json === null,
    'pages.content_format column exists' => zoosper_phase077_column_exists($pdo, 'pages', 'content_format'),
    'pages.content_json column exists' => zoosper_phase077_column_exists($pdo, 'pages', 'content_json'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
