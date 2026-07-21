<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 3);
$uploadService = $basePath . '/packages/zoosper-media/src/Service/MediaUploadService.php';
$cleanupService = $basePath . '/packages/zoosper-media/src/Service/MediaStoredFileCleanupService.php';
$adminController = $basePath . '/packages/zoosper-media/src/Controller/MediaAdminController.php';
$editorController = $basePath . '/packages/zoosper-media/src/Controller/MediaEditorJsUploadController.php';

print "Zoosper media upload failure-path audit\n";
print "=======================================\n\n";

$uploadSource = source($uploadService);
$cleanupSource = source($cleanupService);
$adminSource = source($adminController);
$editorSource = source($editorController);

$checks = [
    'MediaUploadService exists' => is_file($uploadService),
    'MediaStoredFileCleanupService exists' => is_file($cleanupService),
    'upload service stores before repository persistence' => order($uploadSource, '->storage->store', '->assets->create'),
    'upload service catches persistence/storage failures' => str_contains($uploadSource, 'catch (Throwable $exception)'),
    'upload service only cleans when stored object exists' => str_contains($uploadSource, 'if (is_object($stored))'),
    'upload service delegates cleanup to cleanup service' => str_contains($uploadSource, '->cleanup->cleanup($stored)'),
    'upload service logs cleanup attempt' => str_contains($uploadSource, 'cleanup_attempted'),
    'upload service logs deleted file count' => str_contains($uploadSource, 'cleanup_deleted'),
    'upload service logs skipped cleanup count' => str_contains($uploadSource, 'cleanup_skipped'),
    'upload service returns 500 failure after exception' => str_contains($uploadSource, "MediaUploadServiceResult::failure('Unable to store uploaded media file.', 500)"),
    'cleanup service maps public media URLs to public paths' => str_contains($cleanupSource, "str_starts_with(\$storedPath, '/media/')") && str_contains($cleanupSource, "'/public' . \$storedPath"),
    'cleanup service refuses outside-root paths' => str_contains($cleanupSource, 'safeRealpath') && str_contains($cleanupSource, 'str_starts_with($real, $base'),
    'normal admin upload delegates to shared service' => str_contains($adminSource, '->uploads->upload'),
    'editorjs upload delegates to shared service' => str_contains($editorSource, '->uploads->upload'),
];

foreach ($checks as $label => $ok) {
    print '- ' . $label . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
}

print "\nFailure-path order:\n";
print '- storage before persistence: ' . (order($uploadSource, '->storage->store', '->assets->create') ? 'yes' : 'no') . PHP_EOL;
print '- cleanup after exception   : ' . (str_contains($uploadSource, '->cleanup->cleanup($stored)') ? 'yes' : 'no') . PHP_EOL;
print '- admin shared service      : ' . (str_contains($adminSource, '->uploads->upload') ? 'yes' : 'no') . PHP_EOL;
print '- editor shared service     : ' . (str_contains($editorSource, '->uploads->upload') ? 'yes' : 'no') . PHP_EOL;

$failed = in_array(false, $checks, true);
print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function source(string $file): string
{
    return is_file($file) ? (string) file_get_contents($file) : '';
}

function order(string $source, string $first, string $second): bool
{
    $firstPosition = strpos($source, $first);
    $secondPosition = strpos($source, $second);

    return $firstPosition !== false && $secondPosition !== false && $firstPosition < $secondPosition;
}
