<?php

declare(strict_types=1);

/**
 * Add Phase 0.34 module PSR-4 namespaces to composer.json.
 *
 * This script is intentionally narrow and idempotent. It adds/keeps the mail
 * and two-factor namespaces required by Phase 0.34, then Composer should be run
 * separately with `composer dump-autoload`.
 */

$composerFile = dirname(__DIR__) . '/composer.json';

if (!is_file($composerFile)) {
    fwrite(STDERR, "composer.json not found. Run this script from the Zoosper repository root.\n");
    exit(1);
}

$json = file_get_contents($composerFile);
if ($json === false) {
    fwrite(STDERR, "Unable to read composer.json.\n");
    exit(1);
}

$data = json_decode($json, true);
if (!is_array($data)) {
    fwrite(STDERR, "composer.json is not valid JSON.\n");
    exit(1);
}

$data['autoload'] ??= [];
$data['autoload']['psr-4'] ??= [];

if (!is_array($data['autoload']['psr-4'])) {
    fwrite(STDERR, "composer.json autoload.psr-4 must be an object.\n");
    exit(1);
}

$required = [
    'Zoosper\\Mail\\' => 'app/zoosper-mail/src/',
    'Zoosper\\TwoFactor\\' => 'app/zoosper-two-factor/src/',
];

$changed = false;
foreach ($required as $namespace => $path) {
    if (($data['autoload']['psr-4'][$namespace] ?? null) !== $path) {
        $data['autoload']['psr-4'][$namespace] = $path;
        $changed = true;
        echo "Added {$namespace} => {$path}\n";
    } else {
        echo "Autoload mapping already exists: {$namespace} => {$path}\n";
    }
}

if (!$changed) {
    echo "No composer.json changes required.\n";
    exit(0);
}

ksort($data['autoload']['psr-4']);
$encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

if (file_put_contents($composerFile, $encoded) === false) {
    fwrite(STDERR, "Unable to write composer.json.\n");
    exit(1);
}

echo "Updated composer.json. Run composer dump-autoload next.\n";
