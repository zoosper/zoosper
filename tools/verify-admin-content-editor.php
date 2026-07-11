<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin content editor verification\n";
print "=========================================\n\n";

$checks = [
    'config/editor.php' => is_file($basePath . '/config/editor.php'),
    'ContentEditorInterface' => interface_exists(\Zoosper\Admin\Editor\ContentEditorInterface::class),
    'TextareaContentEditor' => class_exists(\Zoosper\Admin\Editor\TextareaContentEditor::class),
    'EditorJsContentEditor' => class_exists(\Zoosper\Admin\Editor\EditorJsContentEditor::class),
    'ContentEditorRegistry' => class_exists(\Zoosper\Admin\Editor\ContentEditorRegistry::class),
    'editor css' => is_file($basePath . '/public/assets/admin/css/zoosper-content-editor.css'),
    'editor js' => is_file($basePath . '/public/assets/admin/js/zoosper-content-editor.js'),
];

$registry = new \Zoosper\Admin\Editor\ContentEditorRegistry(
    new \Zoosper\Admin\Editor\EditorJsContentEditor(new \Zoosper\Admin\Editor\TextareaContentEditor()),
    new \Zoosper\Admin\Editor\TextareaContentEditor(),
);
$checks['registered editors'] = $registry->codes() === ['editorjs', 'textarea'];
$html = $registry->get('editorjs')->render('content', '<p>Hello</p>', ['required' => true]);
$checks['editor render'] = str_contains($html, 'data-zoosper-editor="editorjs"') && str_contains($html, 'name="content"');

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
