<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$package = is_file($basePath . '/package.json') ? json_decode((string) file_get_contents($basePath . '/package.json'), true) : [];
$entry = is_file($basePath . '/assets/admin/editor/zoosper-editorjs-entry.js') ? (string) file_get_contents($basePath . '/assets/admin/editor/zoosper-editorjs-entry.js') : '';
$runtime = is_file($basePath . '/public/assets/admin/js/zoosper-content-editor.js') ? (string) file_get_contents($basePath . '/public/assets/admin/js/zoosper-content-editor.js') : '';

print "Zoosper Editor.js tools verification\n";
print "====================================\n\n";

$checks = [
    '@editorjs/header dependency' => isset($package['dependencies']['@editorjs/header']),
    '@editorjs/list dependency' => isset($package['dependencies']['@editorjs/list']),
    'entry imports Header' => str_contains($entry, "@editorjs/header"),
    'entry imports EditorjsList' => str_contains($entry, "@editorjs/list"),
    'runtime builds tools config' => str_contains($runtime, 'buildToolsConfig'),
    'runtime registers header tool' => str_contains($runtime, 'tools.header'),
    'runtime registers list tool' => str_contains($runtime, 'tools.list'),
    'html bridge supports headings' => str_contains($runtime, "tag === 'h2'") && str_contains($runtime, "type: 'header'"),
    'html bridge supports lists' => str_contains($runtime, "tag === 'ul'") && str_contains($runtime, "type: 'list'"),
    'output bridge renders headings' => str_contains($runtime, "'<h' + level"),
    'output bridge renders lists' => str_contains($runtime, 'renderListItems'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
