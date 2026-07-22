<?php

declare(strict_types=1);

/**
 * Create a write-gated migration plan for one legacy tools/verify-*.php script.
 *
 * This command is read-only. It writes a plan report but never deletes the script.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
$script = null;

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--script=')) {
        $script = substr($argument, strlen('--script='));
    } elseif (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if ($script === null || $script === '') {
    fwrite(STDERR, 'Usage: php tools/plan-legacy-verify-migration.php --script=tools/verify-example.php [--output-dir=/tmp/out]' . PHP_EOL);
    exit(1);
}

$normalisedScript = str_replace('\\', '/', $script);

if (! str_starts_with($normalisedScript, 'tools/verify-') || ! str_ends_with($normalisedScript, '.php')) {
    fwrite(STDERR, 'Refusing to plan non legacy-verify script: ' . $script . PHP_EOL);
    exit(1);
}

$scriptPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalisedScript);

if (! is_file($scriptPath)) {
    fwrite(STDERR, 'Legacy verify script not found: ' . $normalisedScript . PHP_EOL);
    exit(1);
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$contents = (string) file_get_contents($scriptPath);
$baseName = basename($normalisedScript, '.php');
$planPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'legacy-verify-migration-plan-' . $baseName . '.txt';

$plan = [];
$plan[] = '# Legacy Verify Migration Plan';
$plan[] = '';
$plan[] = 'Script: ' . $normalisedScript;
$plan[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$plan[] = 'Bytes: ' . (filesize($scriptPath) ?: 0);
$plan[] = 'Lines: ' . (substr_count($contents, "\n") + 1);
$plan[] = '';
$plan[] = '## Required migration steps';
$plan[] = '';
$plan[] = '1. Read the legacy verify script and identify the source contract it asserts.';
$plan[] = '2. Add or confirm equivalent Pest coverage in the owning module test suite.';
$plan[] = '3. Run the full Pest suite.';
$plan[] = '4. Remove only this legacy verify script after equivalent coverage is green.';
$plan[] = '5. Regenerate tools inventory reports.';
$plan[] = '';
$plan[] = '## Safety gate';
$plan[] = '';
$plan[] = 'This planner does not delete files. Deletion must happen in a focused commit after tests are green.';
$plan[] = '';
$plan[] = '## Source hints';
$plan[] = '';
$plan[] = sourceHintSummary($contents);

file_put_contents($planPath, implode(PHP_EOL, $plan) . PHP_EOL);

echo 'Legacy verify migration plan written to: ' . $planPath . PHP_EOL;

function sourceHintSummary(string $contents): string
{
    $hints = [];

    foreach (['schema', 'route', 'service', 'composer', 'autoload', 'docs', 'roadmap', 'permission', 'public', 'template'] as $needle) {
        if (stripos($contents, $needle) !== false) {
            $hints[] = $needle;
        }
    }

    if ($hints === []) {
        return 'No specific keyword hints found. Treat as a generic source-contract migration.';
    }

    return 'Detected keyword hints: ' . implode(', ', array_values(array_unique($hints))) . '.';
}
