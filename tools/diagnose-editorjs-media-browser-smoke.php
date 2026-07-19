<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper Editor.js media browser smoke diagnostics\n";
print "=================================================\n\n";

$checks = [
    'admin editor runtime adapter exists' => is_file($basePath . '/public/assets/admin/js/zoosper-content-editor.js'),
    'admin editor bundle exists' => is_file($basePath . '/public/assets/admin/js/editorjs.bundle.js'),
    'admin editor css exists' => is_file($basePath . '/public/assets/admin/css/zoosper-content-editor.css'),
    'package.json declares @editorjs/image' => fileContains($basePath . '/package.json', '"@editorjs/image"'),
    'bundle source imports @editorjs/image' => fileContains($basePath . '/assets/admin/editor/zoosper-editorjs-entry.js', "@editorjs/image"),
    'runtime reads image tool data attribute' => fileContains($basePath . '/public/assets/admin/js/zoosper-content-editor.js', 'data-zoosper-image-tool'),
    'runtime registers tools.image' => fileContains($basePath . '/public/assets/admin/js/zoosper-content-editor.js', 'tools.image'),
    'runtime bundle exposes ImageTool' => fileContains($basePath . '/public/assets/admin/js/editorjs.bundle.js', 'ImageTool'),
    'media editorjs upload route exists' => fileContains($basePath . '/packages/zoosper-media/config/admin_routes.php', '/admin/media/editorjs/upload'),
    'media upload controller reads image field' => fileContains($basePath . '/packages/zoosper-media/src/Controller/MediaEditorJsUploadController.php', "\$_FILES['image']"),
    'csrf middleware accepts x-csrf-token' => fileContains($basePath . '/app/zoosper-auth/src/Http/CsrfMiddleware.php', 'x-csrf-token'),
    'admin css contains image tool polish' => fileContains($basePath . '/public/assets/admin/css/zoosper-content-editor.css', '.image-tool'),
    'public media directory exists or can be created' => ensureDirectory($basePath . '/public/media'),
    'private media original directory exists or can be created' => ensureDirectory($basePath . '/storage/media/original'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nManual browser checks still required:\n";
print "- /admin/pages/create loads without console errors.\n";
print "- Image Tool appears in the Editor.js block menu.\n";
print "- Upload request uses field=image and X-CSRF-Token.\n";
print "- Successful response contains success=1 and file.url.\n";
print "- Saved frontend page renders a /media/... image.\n";

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function fileContains(string $path, string $needle): bool
{
    return is_file($path) && str_contains((string) file_get_contents($path), $needle);
}

function ensureDirectory(string $path): bool
{
    return is_dir($path) || @mkdir($path, 0775, true) || is_dir($path);
}
