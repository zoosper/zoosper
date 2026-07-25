<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$errors = [];
$warnings = [];

$expected = [
    'var/reports/application-factory-fallback-cutover-plan.txt',
    'var/reports/application-factory-fallback-cutover-plan.json',
    'var/reports/application-factory-fallback-cutover-draft.patch.md',
];

foreach ($expected as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing expected cutover planning output: ' . $file;
    }
}

$draft = $root . '/var/reports/application-factory-fallback-cutover-draft.patch.md';
if (is_file($draft)) {
    $source = (string) file_get_contents($draft);
    foreach ([
        'FallbackHandlerInterface',
        'NullFallbackHandler',
        'use Zoosper\\Page\\Controller\\PageController;',
    ] as $needle) {
        if (!str_contains($source, $needle)) {
            $errors[] = 'Patch draft is missing expected token: ' . $needle;
        }
    }
} else {
    $warnings[] = 'Patch draft not found. Run plan-application-factory-fallback-cutover.php first.';
}

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$lines = [
    '## ApplicationFactory Fallback Cutover Plan Audit',
    '',
    'Generated: ' . gmdate('c'),
    '',
    'Errors: ' . count($errors),
    'Warnings: ' . count($warnings),
    '',
    '### Errors',
];
foreach ($errors as $error) {
    $lines[] = '- ' . $error;
}
$lines[] = '';
$lines[] = '### Warnings';
foreach ($warnings as $warning) {
    $lines[] = '- ' . $warning;
}

file_put_contents($reportDir . '/application-factory-fallback-cutover-plan-audit.txt', implode("\n", $lines) . "\n");

echo implode("\n", $lines) . "\n";
exit($errors === [] ? 0 : 1);
