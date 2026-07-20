<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controller = $basePath . '/packages/zoosper-media/src/Controller/MediaAdminController.php';
$editorController = $basePath . '/packages/zoosper-media/src/Controller/MediaEditorJsUploadController.php';
$service = $basePath . '/packages/zoosper-media/src/Service/MediaUploadService.php';

print "Zoosper media upload controller duplication audit\n";
print "================================================\n\n";

$checks = [
    'normal media admin controller exists' => is_file($controller),
    'editorjs upload controller exists' => is_file($editorController),
    'shared MediaUploadService exists' => is_file($service),
];

$adminSource = is_file($controller) ? (string) file_get_contents($controller) : '';
$editorSource = is_file($editorController) ? (string) file_get_contents($editorController) : '';
$serviceSource = is_file($service) ? (string) file_get_contents($service) : '';

$adminStillDirect = str_contains($adminSource, '->storage->store') || str_contains($adminSource, '->assets->create');
$editorDelegates = str_contains($editorSource, 'MediaUploadService') && str_contains($editorSource, '->uploads->upload');
$serviceCleans = str_contains($serviceSource, 'MediaStoredFileCleanupService') && str_contains($serviceSource, '->cleanup->cleanup($stored)');

$checks['editorjs controller delegates to shared service'] = $editorDelegates;
$checks['shared service owns orphan cleanup'] = $serviceCleans;
$checks['normal admin upload controller migrated'] = !$adminStillDirect;

foreach ($checks as $label => $ok) {
    print '- ' . $label . ': ' . ($ok ? 'ok' : 'PENDING') . PHP_EOL;
}

print "\nDuplication signals:\n";
print '- MediaAdminController direct storage/assets calls: ' . ($adminStillDirect ? 'yes' : 'no') . PHP_EOL;
print '- MediaEditorJsUploadController shared service delegation: ' . ($editorDelegates ? 'yes' : 'no') . PHP_EOL;
print '- MediaUploadService cleanup delegation: ' . ($serviceCleans ? 'yes' : 'no') . PHP_EOL;

if ($adminStillDirect) {
    print "\nNext action:\n";
    print "- Migrate MediaAdminController::upload() to MediaUploadService so normal admin uploads and Editor.js uploads share validation, persistence and orphan cleanup.\n";
}

$failed = !$checks['normal media admin controller exists'] || !$checks['editorjs upload controller exists'] || !$checks['shared MediaUploadService exists'] || !$editorDelegates || !$serviceCleans;
print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
