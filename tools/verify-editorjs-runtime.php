<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$adapter = is_file($basePath . '/public/assets/admin/js/zoosper-content-editor.js')
    ? (string) file_get_contents($basePath . '/public/assets/admin/js/zoosper-content-editor.js')
    : '';
$css = is_file($basePath . '/public/assets/admin/css/zoosper-content-editor.css')
    ? (string) file_get_contents($basePath . '/public/assets/admin/css/zoosper-content-editor.css')
    : '';

print "Zoosper Editor.js runtime verification\n";
print "======================================\n\n";

$checks = [
    'editor adapter script exists' => $adapter !== '',
    'editor css exists' => $css !== '',
    'EditorJS constructor check' => str_contains($adapter, 'typeof window.EditorJS'),
    'EditorJS initialisation' => str_contains($adapter, 'new window.EditorJS'),
    'uses editor.isReady' => str_contains($adapter, 'editor.isReady.then'),
    'active after ready class' => str_contains($adapter, 'is-editorjs-ready'),
    'aria hidden false on ready' => str_contains($adapter, "aria-hidden', 'false"),
    'fallback keeps textarea available' => str_contains($adapter, 'fallbackToTextarea'),
    'html to editor data bridge' => str_contains($adapter, 'htmlToEditorData'),
    'editor data to html bridge' => str_contains($adapter, 'editorDataToHtml'),
    'textarea sync on change' => str_contains($adapter, 'textarea.value = editorDataToHtml'),
    'form submit sync' => str_contains($adapter, "form.addEventListener('submit'"),
    'ready class hides textarea' => str_contains($css, 'is-editorjs-ready .admin-content-editor--textarea'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
