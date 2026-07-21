<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 3);
require_once $basePath . '/vendor/autoload.php';

$processorFile = $basePath . '/packages/zoosper-media/src/Processing/LocalCopyMediaProcessor.php';
$policyFile = $basePath . '/packages/zoosper-media/src/Processing/MediaProcessingPolicy.php';
$resolverFile = $basePath . '/packages/zoosper-media/src/Processing/LocalMediaDerivativePathResolver.php';
$writerFile = $basePath . '/packages/zoosper-media/src/Processing/LocalMediaDerivativeWriter.php';

$source = is_file($processorFile) ? (string) file_get_contents($processorFile) : '';

print "Zoosper local copy media processor audit\n";
print "========================================\n\n";

$checks = [
    'LocalCopyMediaProcessor exists' => is_file($processorFile),
    'MediaProcessorInterface is implemented' => str_contains($source, 'implements MediaProcessorInterface'),
    'engine-free no-op copy is documented' => str_contains($source, 'performs no resize') && str_contains($source, 'bytes are deliberately unchanged'),
    'MediaProcessingPolicy exists' => is_file($policyFile),
    'Local derivative path resolver exists' => is_file($resolverFile),
    'Local derivative writer exists' => is_file($writerFile),
    'processor uses default processing plan' => str_contains($source, 'defaultPlan()'),
    'processor writes derivatives via writer' => str_contains($source, '->write($target, $contents)'),
    'processor rejects unsafe source paths' => str_contains($source, 'Unsafe source media storage path'),
    'processor keeps originals immutable' => !str_contains($source, 'file_put_contents($source'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
