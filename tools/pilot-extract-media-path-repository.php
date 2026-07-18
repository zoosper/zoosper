<?php

declare(strict_types=1);

/**
 * Phase 1.37f pilot: prepare zoosper-media as the first Composer path repository.
 *
 * This is deliberately conservative. It moves the current app/zoosper-media
 * module to packages/zoosper-media, keeps an app/zoosper-media symlink for the
 * existing ModuleRegistry discovery path, and updates root composer.json with a
 * path repository + require entry for zoosper/media.
 *
 * The symlink keeps runtime behaviour identical while allowing Composer package
 * installation semantics to be tested. A later phase can teach ModuleRegistry to
 * discover vendor/package modules and then remove the compatibility symlink.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$dryRun = in_array('--dry-run', $argv, true);
$force = in_array('--force', $argv, true);

$source = $basePath . '/app/zoosper-media';
$target = $basePath . '/packages/zoosper-media';
$composerFile = $basePath . '/composer.json';

print "Zoosper media path-repository pilot\n";
print "===================================\n\n";
print 'Mode: ' . ($dryRun ? 'dry-run' : 'apply') . PHP_EOL;

if (!is_dir($source) && !is_link($source)) {
    fail('Source module does not exist: app/zoosper-media');
}

if (!is_file($source . '/composer.json') && !is_file($target . '/composer.json')) {
    fail('Media module composer.json is missing. Apply Phase 1.37e first.');
}

$operations = [];
if (!is_dir(dirname($target))) {
    $operations[] = 'create packages/ directory';
}

if (!is_dir($target)) {
    $operations[] = 'move app/zoosper-media to packages/zoosper-media';
    $operations[] = 'create compatibility symlink app/zoosper-media -> ../packages/zoosper-media';
} elseif (!$force) {
    $operations[] = 'packages/zoosper-media already exists; leave files untouched';
}

$operations[] = 'ensure root composer.json has path repository packages/zoosper-media';
$operations[] = 'ensure root composer.json requires zoosper/media *@dev';

foreach ($operations as $operation) {
    print '- ' . $operation . PHP_EOL;
}

if ($dryRun) {
    print "\nDry-run only. No files changed.\n";
    exit(0);
}

if (!is_dir(dirname($target))) {
    mkdir(dirname($target), 0775, true);
}

if (!is_dir($target)) {
    if (is_link($source)) {
        fail('app/zoosper-media is already a symlink but packages/zoosper-media does not exist. Fix manually before continuing.');
    }

    if (!rename($source, $target)) {
        fail('Unable to move app/zoosper-media to packages/zoosper-media.');
    }

    $relativeTarget = '../packages/zoosper-media';
    if (!symlink($relativeTarget, $source)) {
        // Roll back the move if symlink creation fails.
        rename($target, $source);
        fail('Unable to create compatibility symlink app/zoosper-media. Move was rolled back.');
    }
}

updateComposer($composerFile);

print "\nPilot extraction complete.\n";
print "Next commands:\n";
print "  PHP=php8.5 composer update zoosper/media --with-dependencies\n";
print "  PHP=php8.5 composer dump-autoload\n";
print "  php8.5 tools/verify-media-path-repository-pilot.php\n";
print "  PHP=php8.5 bin/verify\n";

function updateComposer(string $composerFile): void
{
    if (!is_file($composerFile)) {
        fail('composer.json not found.');
    }

    $json = json_decode((string) file_get_contents($composerFile), true);
    if (!is_array($json)) {
        fail('composer.json could not be decoded.');
    }

    $json['repositories'] ??= [];
    if (!is_array($json['repositories'])) {
        $json['repositories'] = [];
    }

    $repoExists = false;
    foreach ($json['repositories'] as $repository) {
        if (is_array($repository)
            && ($repository['type'] ?? null) === 'path'
            && ($repository['url'] ?? null) === 'packages/zoosper-media') {
            $repoExists = true;
            break;
        }
    }

    if (!$repoExists) {
        $json['repositories'][] = [
            'type' => 'path',
            'url' => 'packages/zoosper-media',
            'options' => ['symlink' => true],
        ];
    }

    $json['require'] ??= [];
    if (!is_array($json['require'])) {
        $json['require'] = [];
    }
    $json['require']['zoosper/media'] = '*@dev';

    file_put_contents($composerFile, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
}

function fail(string $message): never
{
    fwrite(STDERR, "ERROR: {$message}\n");
    exit(1);
}
