<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 3);
$package = $basePath . '/packages/zoosper-media';
$smokeFile = $package . '/tools/smoke-local-copy-derivative-generation.php';
$dispatcherFile = $package . '/src/Processing/MediaUploadDerivativeDispatcher.php';
$policyFile = $package . '/src/Processing/MediaUploadDerivativePolicy.php';
$processorFile = $package . '/src/Processing/LocalCopyMediaProcessor.php';

$smoke = is_file($smokeFile) ? (string) file_get_contents($smokeFile) : '';

print "Zoosper local copy derivative smoke audit\n";
print "=========================================\n\n";

$checks = [
    'smoke tool exists' => is_file($smokeFile),
    'upload derivative dispatcher exists' => is_file($dispatcherFile),
    'upload derivative policy exists' => is_file($policyFile),
    'local copy processor exists' => is_file($processorFile),
    'smoke enables policy explicitly' => str_contains($smoke, 'new MediaUploadDerivativePolicy(true)'),
    'smoke uses upload derivative dispatcher' => str_contains($smoke, 'new MediaUploadDerivativeDispatcher'),
    'smoke uses local copy processor' => str_contains($smoke, 'new LocalCopyMediaProcessor'),
    'smoke writes under var smoke directory' => str_contains($smoke, '/var/smoke/media-derivatives'),
    'smoke creates a tiny png fixture' => str_contains($smoke, 'iVBORw0KGgo'),
    'smoke checks derivative files were created' => str_contains($smoke, 'derivative files created'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
