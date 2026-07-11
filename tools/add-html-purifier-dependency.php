<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);
$composerJson = $basePath . '/composer.json';

if (!is_file($composerJson)) {
    fwrite(STDERR, "composer.json not found.\n");
    exit(2);
}

$data = json_decode((string) file_get_contents($composerJson), true, 512, JSON_THROW_ON_ERROR);
$data['require'] ??= [];

if (isset($data['require']['ezyang/htmlpurifier'])) {
    print "ezyang/htmlpurifier is already listed: " . $data['require']['ezyang/htmlpurifier'] . PHP_EOL;
    exit(0);
}

$data['require']['ezyang/htmlpurifier'] = '^4.19';
ksort($data['require']);
file_put_contents($composerJson, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
print "Added ezyang/htmlpurifier:^4.19 to composer.json.\n";
print "Next: composer update ezyang/htmlpurifier && composer dump-autoload\n";
