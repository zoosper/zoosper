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
$errors = 0;
$report = [];
$report[] = '## Admin Middleware Config Normaliser';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = 'Mode: ' . ($dryRun ? 'dry-run' : 'apply');
$report[] = '';

$normaliseEntry = static function (mixed $entry): ?string {
    if (is_string($entry) && $entry !== '') {
        return $entry;
    }

    if (is_array($entry)) {
        foreach (['class', 'middleware', 'handler'] as $key) {
            if (isset($entry[$key]) && is_string($entry[$key]) && $entry[$key] !== '') {
                return $entry[$key];
            }
        }

        if (count($entry) === 1) {
            $only = array_values($entry)[0];
            if (is_string($only) && $only !== '') {
                return $only;
            }
        }
    }

    return null;
};

foreach ($files as $file) {
    $relative = str_replace($root . '/', '', $file);
    $config = require $file;

    if (!is_array($config)) {
        $report[] = 'SKIP non-array config: ' . $relative;
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
    foreach (array_values($entries) as $index => $entry) {
        $value = $normaliseEntry($entry);
        if ($value === null) {
            $report[] = 'ERROR cannot normalise ' . $relative . ' entry ' . $index . ': ' . trim(var_export($entry, true));
            $errors++;
            continue 2;
        }
        $normalised[] = $value;
    }

    if ($normalised === $config) {
        $report[] = 'OK already normalised: ' . $relative;
        continue;
    }

    $changed++;
    $report[] = ($dryRun ? 'DRY-RUN would normalise: ' : 'NORMALISED: ') . $relative;

    if (!$dryRun) {
        $backup = $file . '.phase141mw.bak';
        if (!is_file($backup)) {
            file_put_contents($backup, (string) file_get_contents($file));
        }

        $php = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($normalised, true) . ";\n";
        file_put_contents($file, $php);
    }
}

$report[] = '';
$report[] = 'Changed: ' . $changed;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/admin-middleware-config-normalise.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/admin-middleware-config-normalise.log', "ADMIN_MIDDLEWARE_CONFIG_NORMALISE_CHANGED {$changed}\nADMIN_MIDDLEWARE_CONFIG_NORMALISE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
