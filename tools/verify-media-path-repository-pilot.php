<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$composer = is_file($basePath . '/composer.json') ? json_decode((string) file_get_contents($basePath . '/composer.json'), true) : [];
$composer = is_array($composer) ? $composer : [];

print "Zoosper media path-repository pilot verification\n";
print "================================================\n\n";

$checks = [
    'packages/zoosper-media exists' => is_dir($basePath . '/packages/zoosper-media'),
    'packages/zoosper-media/module.php exists' => is_file($basePath . '/packages/zoosper-media/module.php'),
    'packages/zoosper-media/composer.json exists' => is_file($basePath . '/packages/zoosper-media/composer.json'),
    'app/zoosper-media compatibility path exists' => is_dir($basePath . '/app/zoosper-media') || is_link($basePath . '/app/zoosper-media'),
    'root composer requires zoosper/media' => (($composer['require']['zoosper/media'] ?? null) === '*@dev'),
    'root composer has media path repository' => hasMediaRepository($composer['repositories'] ?? []),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function hasMediaRepository(mixed $repositories): bool
{
    if (!is_array($repositories)) {
        return false;
    }

    foreach ($repositories as $repository) {
        if (is_array($repository)
            && ($repository['type'] ?? null) === 'path'
            && ($repository['url'] ?? null) === 'packages/zoosper-media') {
            return true;
        }
    }

    return false;
}
