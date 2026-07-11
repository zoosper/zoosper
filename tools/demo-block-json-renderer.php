<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$document = [
    'time' => time(),
    'blocks' => [
        ['type' => 'header', 'data' => ['text' => 'Zoosper block content', 'level' => 2]],
        ['type' => 'paragraph', 'data' => ['text' => 'This is a paragraph generated from block JSON.']],
        ['type' => 'list', 'data' => ['style' => 'ordered', 'items' => [
            ['content' => 'Validate blocks', 'meta' => [], 'items' => []],
            ['content' => 'Render safe HTML', 'meta' => [], 'items' => []],
        ]]],
    ],
    'version' => '2.x',
];

$config = require __DIR__ . '/../config/content_model.php';
$validator = new \Zoosper\Page\Content\BlockJsonValidator($config['block_json'] ?? []);
$result = $validator->validate($document);

print "Zoosper block JSON renderer demo\n";
print "================================\n\n";
print 'Validation: ' . ($result->valid ? 'OK' : 'FAIL') . PHP_EOL . PHP_EOL;
print (new \Zoosper\Page\Content\BlockJsonToHtmlRenderer())->render($document) . PHP_EOL;
