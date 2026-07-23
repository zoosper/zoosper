<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$apply = in_array('--apply', $argv, true);
$dryRun = in_array('--dry-run', $argv, true) || !$apply;
$patterns = [
    $root . '/app/*/config/admin_middleware.php',
    $root . '/packages/*/config/admin_middleware.php',
    $root . '/packages/*/*/config/admin_middleware.php',
];

$files = [];
foreach ($patterns as $pattern) {
    foreach (glob($pattern) ?: [] as $file) {
        $files[$file] = $file;
    }
}
ksort($files);

$changed = 0;
$removed = 0;
$errors = 0;
$report = [];
$report[] = '## Remove Closure Admin Middleware Entries';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = 'Mode: ' . ($dryRun ? 'dry-run' : 'apply');
$report[] = '';

foreach ($files as $file) {
    $relative = str_replace($root . '/', '', $file);

    try {
        $config = require $file;
    } catch (Throwable $exception) {
        $report[] = 'ERROR loading ' . $relative . ': ' . $exception->getMessage();
        $errors++;
        continue;
    }

    if (!is_array($config)) {
        $report[] = 'ERROR non-array admin middleware config: ' . $relative;
        $errors++;
        continue;
    }

    $entries = $config;
    if (isset($entries['admin']) && is_array($entries['admin'])) {
        $entries = $entries['admin'];
    } elseif (isset($entries['middleware']) && is_array($entries['middleware'])) {
        $entries = $entries['middleware'];
    }

    $normalised = [];
    $fileRemoved = 0;

    foreach (array_values($entries) as $index => $entry) {
        if ($entry instanceof Closure) {
            $report[] = ($dryRun ? 'DRY-RUN would remove' : 'REMOVED') . ' Closure entry ' . $index . ' from ' . $relative;
            $fileRemoved++;
            continue;
        }

        if (!is_string($entry) || $entry === '') {
            $report[] = 'ERROR remaining invalid entry ' . $index . ' in ' . $relative . ': ' . trim(var_export($entry, true));
            $errors++;
            continue 2;
        }

        $normalised[] = $entry;
    }

    if ($fileRemoved === 0) {
        $report[] = 'OK no Closure entries: ' . $relative;
        continue;
    }

    $changed++;
    $removed += $fileRemoved;

    if (!$dryRun) {
        $backup = $file . '.phase141mwclosure.bak';
        if (!is_file($backup)) {
            file_put_contents($backup, (string) file_get_contents($file));
        }

        $php = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($normalised, true) . ";\n";
        file_put_contents($file, $php);
    }
}

$report[] = '';
$report[] = 'Files changed: ' . $changed;
$report[] = 'Closure entries removed: ' . $removed;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/remove-closure-admin-middleware-entries.txt', implode("\n", $report) . "\n");
file_put_contents(
    $reportDir . '/remove-closure-admin-middleware-entries.log',
    "REMOVE_CLOSURE_ADMIN_MIDDLEWARE_CHANGED {$changed}\n" .
    "REMOVE_CLOSURE_ADMIN_MIDDLEWARE_REMOVED {$removed}\n" .
    "REMOVE_CLOSURE_ADMIN_MIDDLEWARE_ERRORS {$errors}\n"
);

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
