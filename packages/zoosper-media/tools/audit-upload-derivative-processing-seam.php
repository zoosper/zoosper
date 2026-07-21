<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 3);
require_once $basePath . '/vendor/autoload.php';

$package = $basePath . '/packages/zoosper-media';
$dispatcherFile = $package . '/src/Processing/MediaUploadDerivativeDispatcher.php';
$policyFile = $package . '/src/Processing/MediaUploadDerivativePolicy.php';
$processorFile = $package . '/src/Processing/LocalCopyMediaProcessor.php';
$serviceFile = $package . '/src/Service/MediaUploadService.php';
$applyToolFile = $package . '/tools/apply-upload-derivative-processing-seam.php';

$dispatcher = is_file($dispatcherFile) ? (string) file_get_contents($dispatcherFile) : '';
$policy = is_file($policyFile) ? (string) file_get_contents($policyFile) : '';
$service = is_file($serviceFile) ? (string) file_get_contents($serviceFile) : '';

print "Zoosper upload derivative processing seam audit\n";
print "================================================\n\n";

$checks = [
    'MediaUploadDerivativeDispatcher exists' => is_file($dispatcherFile),
    'MediaUploadDerivativePolicy exists' => is_file($policyFile),
    'LocalCopyMediaProcessor exists' => is_file($processorFile),
    'dispatcher delegates to MediaProcessorInterface' => str_contains($dispatcher, 'MediaProcessorInterface') && str_contains($dispatcher, '->process($storagePath'),
    'dispatcher has disabled-by-default branch' => str_contains($dispatcher, 'new MediaUploadDerivativePolicy(false)') && str_contains($dispatcher, 'MediaProcessingResult::success([])'),
    'policy is disabled by default' => str_contains($policy, 'private bool $enabled = false'),
    'upload service currently mentions derivative dispatcher' => str_contains($service, 'MediaUploadDerivativeDispatcher'),
    'migration helper exists for upload service seam' => is_file($applyToolFile),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
if (!$checks['upload service currently mentions derivative dispatcher']) {
    print "\nNext: run packages/zoosper-media/tools/apply-upload-derivative-processing-seam.php --write after reviewing dry-run output.\n";
}
exit($failed ? 2 : 0);
