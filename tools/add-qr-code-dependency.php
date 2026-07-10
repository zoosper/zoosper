<?php

declare(strict_types=1);

/**
 * Add the local QR code dependency to composer.json.
 *
 * The dependency is used to render TOTP setup QR codes locally. External QR
 * services must not be used because the provisioning URI contains the TOTP
 * secret. This script does not print or log secrets.
 */

$composerFile = dirname(__DIR__) . '/composer.json';
$data = json_decode((string) file_get_contents($composerFile), true);
if (!is_array($data)) {
    fwrite(STDERR, "composer.json is not valid JSON.\n");
    exit(1);
}

$data['require'] ??= [];
$data['require']['bacon/bacon-qr-code'] ??= '^3.0';
ksort($data['require']);

file_put_contents($composerFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
print "Added bacon/bacon-qr-code to composer.json if it was missing. Run composer update bacon/bacon-qr-code.\n";
