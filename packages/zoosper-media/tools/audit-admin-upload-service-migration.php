<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 3);
$controller = $basePath . '/packages/zoosper-media/src/Controller/MediaAdminController.php';
$editorController = $basePath . '/packages/zoosper-media/src/Controller/MediaEditorJsUploadController.php';
$service = $basePath . '/packages/zoosper-media/src/Service/MediaUploadService.php';
$cleanup = $basePath . '/packages/zoosper-media/src/Service/MediaStoredFileCleanupService.php';

print "Zoosper media admin upload service migration audit\n";
print "=================================================\n\n";

$adminSource = is_file($controller) ? (string) file_get_contents($controller) : '';
$editorSource = is_file($editorController) ? (string) file_get_contents($editorController) : '';
$serviceSource = is_file($service) ? (string) file_get_contents($service) : '';
$cleanupSource = is_file($cleanup) ? (string) file_get_contents($cleanup) : '';

$checks = [
    'MediaAdminController exists' => is_file($controller),
    'MediaUploadService exists' => is_file($service),
    'MediaStoredFileCleanupService exists' => is_file($cleanup),
    'admin controller delegates to MediaUploadService' => str_contains($adminSource, 'MediaUploadService') && str_contains($adminSource, '->uploads->upload'),
    'admin controller no longer writes storage directly' => !str_contains($adminSource, '->storage->store'),
    'admin controller no longer creates assets directly' => !str_contains($adminSource, '->assets->create'),
    'admin controller removed duplicate filename normaliser' => !str_contains($adminSource, 'normaliseOriginalFilename'),
    'editorjs controller delegates to MediaUploadService' => str_contains($editorSource, 'MediaUploadService') && str_contains($editorSource, '->uploads->upload'),
    'upload service delegates cleanup on persistence failure' => str_contains($serviceSource, '->cleanup->cleanup($stored)'),
    'cleanup service protects project root' => str_contains($cleanupSource, 'safeRealpath') && str_contains($cleanupSource, 'str_starts_with($real, $base'),
];

foreach ($checks as $label => $ok) {
    print '- ' . $label . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
}

print "\nSignals:\n";
print '- admin direct storage calls: ' . (str_contains($adminSource, '->storage->store') ? 'yes' : 'no') . PHP_EOL;
print '- admin direct asset writes : ' . (str_contains($adminSource, '->assets->create') ? 'yes' : 'no') . PHP_EOL;
print '- admin shared upload call  : ' . (str_contains($adminSource, '->uploads->upload') ? 'yes' : 'no') . PHP_EOL;
print '- service cleanup call      : ' . (str_contains($serviceSource, '->cleanup->cleanup($stored)') ? 'yes' : 'no') . PHP_EOL;

$failed = in_array(false, $checks, true);
print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
