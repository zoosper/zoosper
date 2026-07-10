<?php

declare(strict_types=1);

/**
 * Add the Zoosper\Mail PSR-4 namespace to composer.json.
 *
 * This script is intentionally narrow and idempotent. It updates composer.json
 * only when the mail namespace is missing, then leaves Composer to regenerate
 * the autoload files through `composer dump-autoload`.
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

$namespace = 'Zoosper\\Mail\\';
$path = 'app/zoosper-mail/src/';

if (($data['autoload']['psr-4'][$namespace] ?? null) === $path) {
    echo "Zoosper mail autoload mapping already exists.\n";
    exit(0);
}

$data['autoload']['psr-4'][$namespace] = $path;
ksort($data['autoload']['psr-4']);

$encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
if (file_put_contents($composerFile, $encoded) === false) {
    fwrite(STDERR, "Unable to write composer.json.\n");
    exit(1);
}

echo "Added {$namespace} => {$path} to composer.json.\n";
