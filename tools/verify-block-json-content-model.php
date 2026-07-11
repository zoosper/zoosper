<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$config = is_file($basePath . '/config/content_model.php') ? require $basePath . '/config/content_model.php' : [];

print "Zoosper block JSON content model verification\n";
print "=============================================\n\n";

$sample = [
    'time' => time(),
    'blocks' => [
        ['type' => 'header', 'data' => ['text' => 'Heading', 'level' => 2]],
        ['type' => 'paragraph', 'data' => ['text' => 'Paragraph text']],
        ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => [['content' => 'Item', 'meta' => [], 'items' => []]]]],
    ],
    'version' => '2.x',
];

$validator = new \Zoosper\Page\Content\BlockJsonValidator($config['block_json'] ?? []);
$result = $validator->validate($sample);
$html = (new \Zoosper\Page\Content\BlockJsonToHtmlRenderer())->render($sample);

$checks = [
    'config/content_model.php' => is_file($basePath . '/config/content_model.php'),
    'ContentFormat enum' => enum_exists(\Zoosper\Page\Content\ContentFormat::class),
    'BlockJsonValidationResult' => class_exists(\Zoosper\Page\Content\BlockJsonValidationResult::class),
    'BlockJsonValidator' => class_exists(\Zoosper\Page\Content\BlockJsonValidator::class),
    'BlockJsonToHtmlRenderer' => class_exists(\Zoosper\Page\Content\BlockJsonToHtmlRenderer::class),
    'sample validates' => $result->valid,
    'sample renders h2' => str_contains($html, '<h2>Heading</h2>'),
    'sample renders paragraph' => str_contains($html, '<p>Paragraph text</p>'),
    'sample renders list' => str_contains($html, '<ul><li>Item</li></ul>'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

if (!$result->valid) {
    print "\nValidation errors:\n";
    foreach ($result->errors as $error) {
        print '- ' . $error . PHP_EOL;
    }
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
