<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper documentation package ownership audit\n";
print "=============================================\n\n";

$packageDocs = $basePath . '/packages/zoosper-media/docs';
$rootDocs = [
    $basePath . '/docs/architecture',
    $basePath . '/docs/operations',
    $basePath . '/docs/roadmap',
];

$mediaRootDocs = [];
foreach ($rootDocs as $directory) {
    foreach (glob($directory . '/*media*.md') ?: [] as $file) {
        $mediaRootDocs[] = str_replace($basePath . '/', '', $file);
    }
}

$checks = [
    'package docs directory exists' => is_dir($packageDocs),
    'package architecture docs directory exists' => is_dir($packageDocs . '/architecture'),
    'package operations docs directory exists' => is_dir($packageDocs . '/operations'),
    'media package docs index exists' => is_file($packageDocs . '/README.md'),
    'root package docs policy exists' => is_file($basePath . '/docs/architecture/package-owned-documentation.md'),
];

foreach ($checks as $label => $ok) {
    print '- ' . $label . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
}

print "\nMedia docs still present under root docs:\n" . PHP_EOL;
foreach ($mediaRootDocs as $doc) {
    print '- ' . $doc . PHP_EOL;
}
if ($mediaRootDocs === []) {
    print '- none' . PHP_EOL;
}

print "\nResult: " . (in_array(false, $checks, true) ? 'FAIL' : 'OK') . PHP_EOL;
exit(in_array(false, $checks, true) ? 2 : 0);
