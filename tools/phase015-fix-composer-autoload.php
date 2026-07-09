<?php

declare(strict_types=1);

$composerFile = dirname(__DIR__) . '/composer.json';
if (!is_file($composerFile)) {
    fwrite(STDERR, "composer.json not found. Run from repository root.\n");
    exit(1);
}

$data = json_decode((string) file_get_contents($composerFile), true, 512, JSON_THROW_ON_ERROR);
$data['autoload'] ??= [];
$data['autoload']['psr-4'] ??= [];

$required = [
    'Zoosper\\Theme\\' => 'app/zoosper-theme/src/',
];

$changed = false;
foreach ($required as $namespace => $path) {
    if (($data['autoload']['psr-4'][$namespace] ?? null) !== $path) {
        $data['autoload']['psr-4'][$namespace] = $path;
        $changed = true;
    }
}

if (!$changed) {
    echo "Composer autoload already contains Phase 0.15 namespace mappings.\n";
    exit(0);
}

file_put_contents(
    $composerFile,
    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL,
);

echo "Updated composer.json PSR-4 autoload mappings. Run: composer dump-autoload\n";
