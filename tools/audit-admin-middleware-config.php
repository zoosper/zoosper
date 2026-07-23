<?php

declare(strict_types=1);

$root = dirname(__DIR__);
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

$errors = 0;
$normalisable = 0;
$report = [];
$report[] = '## Admin Middleware Config Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Files scanned: ' . count($files);

$describe = static function (mixed $value): string {
    return trim(var_export($value, true));
};

$isValidEntry = static function (mixed $entry): bool {
    return is_string($entry) && $entry !== '';
};

$isNormalisableEntry = static function (mixed $entry): bool {
    if (is_array($entry)) {
        foreach (['class', 'middleware', 'handler'] as $key) {
            if (isset($entry[$key]) && is_string($entry[$key]) && $entry[$key] !== '') {
                return true;
            }
        }

        if (count($entry) === 1) {
            $only = array_values($entry)[0];
            return is_string($only) && $only !== '';
        }
    }

    return false;
};

foreach ($files as $file) {
    $relative = str_replace($root . '/', '', $file);
    $report[] = '';
    $report[] = '### ' . $relative;

    try {
        $config = require $file;
    } catch (Throwable $exception) {
        $report[] = '- load error: ' . $exception->getMessage();
        $errors++;
        continue;
    }

    if (!is_array($config)) {
        $report[] = '- invalid: config must return array, got ' . get_debug_type($config);
        $errors++;
        continue;
    }

    $entries = $config;
    if (isset($entries['admin']) && is_array($entries['admin'])) {
        $report[] = '- wrapper detected: admin';
        $entries = $entries['admin'];
        $normalisable++;
    } elseif (isset($entries['middleware']) && is_array($entries['middleware'])) {
        $report[] = '- wrapper detected: middleware';
        $entries = $entries['middleware'];
        $normalisable++;
    }

    if ($entries === []) {
        $report[] = '- entries: 0';
        continue;
    }

    $report[] = '- entries: ' . count($entries);

    foreach (array_values($entries) as $index => $entry) {
        if ($isValidEntry($entry)) {
            $report[] = '  - [' . $index . '] valid string: ' . $entry;
            continue;
        }

        if ($isNormalisableEntry($entry)) {
            $report[] = '  - [' . $index . '] normalisable: ' . $describe($entry);
            $normalisable++;
            continue;
        }

        $report[] = '  - [' . $index . '] invalid: ' . $describe($entry);
        $errors++;
    }
}

$report[] = '';
$report[] = 'Normalisable: ' . $normalisable;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/admin-middleware-config-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/admin-middleware-config-audit.log', "ADMIN_MIDDLEWARE_CONFIG_AUDIT_NORMALISABLE {$normalisable}\nADMIN_MIDDLEWARE_CONFIG_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
