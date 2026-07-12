<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper editor interface contract verification\n";
print "==============================================\n\n";

$checks = [];
$classes = [
    \Zoosper\Admin\Editor\TextareaContentEditor::class,
    \Zoosper\Admin\Editor\EditorJsContentEditor::class,
];

foreach ($classes as $class) {
    $instance = new $class();
    $checks[$class . ' implements ContentEditorInterface'] = $instance instanceof \Zoosper\Admin\Editor\ContentEditorInterface;
    $checks[$class . ' code is non-empty'] = trim($instance->code()) !== '';
    $checks[$class . ' render contains textarea'] = str_contains($instance->render('content', '<p>Hello</p>', ['required' => true]), 'textarea');
}

$editorJs = new \Zoosper\Admin\Editor\EditorJsContentEditor();
$editorJsHtml = $editorJs->render('content', '<p>Hello</p>', ['content_json' => '{"blocks":[]}']);
$checks['EditorJsContentEditor code is editorjs'] = $editorJs->code() === 'editorjs';
$checks['EditorJsContentEditor renders content_json hidden field'] = str_contains($editorJsHtml, 'name="content_json"');
$checks['EditorJsContentEditor renders editorjs wrapper'] = str_contains($editorJsHtml, 'data-zoosper-editor="editorjs"');

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
