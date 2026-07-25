<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$provider = $root . '/app/zoosper-page/src/Admin/PageMomentum/PageMomentumDashboardFactsProvider.php';
$fact = $root . '/app/zoosper-page/src/Admin/PageMomentum/PageMomentumDashboardFact.php';

$errors = [];
$warnings = [];

foreach ([$provider, $fact] as $file) {
    if (!is_file($file)) {
        $errors[] = "Missing required file: {$file}";
    }
}

if (is_file($provider)) {
    $source = file_get_contents($provider) ?: '';

    $forbiddenWriteTokens = [
        ' INSERT ',
        ' UPDATE ',
        ' DELETE ',
        ' REPLACE ',
        ' ALTER ',
        ' DROP ',
        ' TRUNCATE ',
        ' CREATE TABLE ',
    ];

    foreach ($forbiddenWriteTokens as $token) {
        if (stripos($source, $token) !== false) {
            $errors[] = "Provider appears to contain write/schema SQL token: {$token}";
        }
    }

    foreach (['facts()', 'factsAsArray()', 'tableExists(', 'columnExists('] as $needle) {
        if (strpos($source, $needle) === false) {
            $errors[] = "Provider is missing expected method/signature fragment: {$needle}";
        }
    }

    if (strpos($source, 'Throwable') === false) {
        $warnings[] = 'Provider should fail soft with Throwable guards.';
    }

    if (strpos($source, 'readonly') === false) {
        $warnings[] = 'Provider/value object should keep immutable/read-only style where practical.';
    }
}

echo "## Page Momentum Dashboard Facts Provider Audit\n";
echo 'Generated: ' . gmdate('c') . "\n\n";
echo $errors === [] ? "Status: PASS\n" : "Status: FAIL\n";

echo "\nErrors: " . count($errors) . "\n";
foreach ($errors as $error) {
    echo "- {$error}\n";
}

echo "\nWarnings: " . count($warnings) . "\n";
foreach ($warnings as $warning) {
    echo "- {$warning}\n";
}

exit($errors === [] ? 0 : 1);
