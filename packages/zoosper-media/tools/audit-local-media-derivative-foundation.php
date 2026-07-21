<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 3);
$resolver = $basePath . '/packages/zoosper-media/src/Processing/LocalMediaDerivativePathResolver.php';
$writer = $basePath . '/packages/zoosper-media/src/Processing/LocalMediaDerivativeWriter.php';
$policy = $basePath . '/packages/zoosper-media/src/Processing/MediaProcessingPolicy.php';
$interface = $basePath . '/packages/zoosper-media/src/Processing/MediaProcessorInterface.php';

print "Zoosper local media derivative foundation audit\n";
print "===============================================\n\n";

$resolverSource = source($resolver);
$writerSource = source($writer);
$policySource = source($policy);
$interfaceSource = source($interface);

$checks = [
    'local derivative path resolver exists' => is_file($resolver),
    'local derivative writer exists' => is_file($writer),
    'media processing policy exists' => is_file($policy),
    'media processor interface exists' => is_file($interface),
    'resolver rejects traversal paths' => str_contains($resolverSource, "str_contains($" . "storagePath, '..')"),
    'resolver rejects absolute source paths' => str_contains($resolverSource, 'str_starts_with($storagePath,') && str_contains($resolverSource, "'/')"),
    'resolver writes under storage media derivatives' => str_contains($resolverSource, 'storage/media/derivatives/'),
    'resolver exposes public derivative path' => str_contains($resolverSource, '/media/derivatives/'),
    'writer creates derivative directory' => str_contains($writerSource, 'mkdir($directory, 0775, true)'),
    'writer rejects empty derivative contents' => str_contains($writerSource, 'Derivative contents cannot be empty'),
    'policy still owns derivative profile definitions' => str_contains($policySource, 'derivative') || str_contains($policySource, 'profile'),
    'processor interface remains available for future engines' => $interfaceSource !== '',
];

foreach ($checks as $label => $ok) {
    print '- ' . $label . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
}

$failed = in_array(false, $checks, true);
print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function source(string $file): string
{
    return is_file($file) ? (string) file_get_contents($file) : '';
}
