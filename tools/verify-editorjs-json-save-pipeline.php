<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

$runtime = (string) file_get_contents($basePath . '/public/assets/admin/js/zoosper-content-editor.js');
$controller = (string) file_get_contents($basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php');
$repository = (string) file_get_contents($basePath . '/app/zoosper-page/src/Repository/PageRepository.php');
$editor = new \Zoosper\Admin\Editor\EditorJsContentEditor();
$editorHtml = $editor->render('content', '<p>Hello</p>', [
    'label' => 'Content',
    'required' => true,
    'content_json' => '{"blocks":[]}',
]);

print "Zoosper Editor.js JSON save pipeline verification\n";
print "=================================================\n\n";

$checks = [
    'EditorJsContentEditor implements editor interface' => $editor instanceof \Zoosper\Admin\Editor\ContentEditorInterface,
    'EditorJsContentEditor code is editorjs' => $editor->code() === 'editorjs',
    'EditorJsContentEditor hidden content_json field' => str_contains($editorHtml, 'name="content_json"'),
    'EditorJsContentEditor keeps textarea fallback' => str_contains($editorHtml, '<textarea') && str_contains($editorHtml, 'name="content"'),
    'EditorJsContentEditor renders editorjs wrapper' => str_contains($editorHtml, 'data-zoosper-editor="editorjs"'),
    'Runtime finds content_json input' => str_contains($runtime, 'input[name="content_json"'),
    'Runtime writes JSON.stringify payload' => str_contains($runtime, 'JSON.stringify(data)'),
    'Runtime parses initial JSON' => str_contains($runtime, 'parseInitialJson'),
    'Controller imports BlockJsonValidator' => str_contains($controller, 'BlockJsonValidator'),
    'Controller reads content_json' => str_contains($controller, "form['content_json']"),
    'Controller validates content_json' => str_contains($controller, 'normaliseContentJson'),
    'Repository accepts contentFormat' => str_contains($repository, 'string $contentFormat'),
    'Repository persists content_json' => str_contains($repository, 'content_json'),
    'SEO section preserved' => str_contains($controller, 'Search engine optimisation'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
