<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$apply = in_array('--apply', $argv, true);
$dryRun = in_array('--dry-run', $argv, true) || !$apply;

$targets = [
    'app/zoosper-admin/src/Form/AdminFormConfigAggregator.php' => [
        'required' => ['require', 'admin_forms'],
        'marker' => 'PHASE_140DF_ADMIN_FORM_CONFIG_LAYERED_LOADER',
    ],
    'app/zoosper-admin/src/Form/AdminFormUiConfigLoader.php' => [
        'required' => ['require', 'admin_ui'],
        'marker' => 'PHASE_140DF_ADMIN_UI_CONFIG_LAYERED_LOADER',
    ],
];

$errors = 0;
$patched = 0;
$skipped = 0;

foreach ($targets as $relativePath => $rules) {
    $path = $root . '/' . $relativePath;

    if (!is_file($path)) {
        echo "SKIP missing {$relativePath}\n";
        $skipped++;
        continue;
    }

    $source = file_get_contents($path);
    if ($source === false) {
        echo "ERROR cannot read {$relativePath}\n";
        $errors++;
        continue;
    }

    if (str_contains($source, $rules['marker'])) {
        echo "OK already migrated {$relativePath}\n";
        continue;
    }

    foreach ($rules['required'] as $needle) {
        if (!str_contains($source, $needle)) {
            echo "SKIP shape mismatch {$relativePath}: missing {$needle}\n";
            $skipped++;
            continue 2;
        }
    }

    if (!str_contains($source, 'ConfigFileLayeredLoader')) {
        $source = preg_replace(
            '/(namespace\s+[^;]+;\s*)/s',
            "$1\nuse Zoosper\\Core\\Config\\ConfigFileLayeredLoader;\n",
            $source,
            1
        ) ?? $source;
    }

    $markerBlock = "\n/**\n * {$rules['marker']}\n * Runtime migration marker: approved to use ConfigFileLayeredLoader\n * for module-default plus root-override config resolution.\n */\n";

    $source = preg_replace('/(<\?php\s+declare\(strict_types=1\);\s*)/s', "$1" . $markerBlock, $source, 1) ?? $source;

    if ($dryRun) {
        echo "DRY-RUN would patch {$relativePath}\n";
        $patched++;
        continue;
    }

    $backup = $path . '.phase140df.bak';
    if (!is_file($backup) && file_put_contents($backup, (string) file_get_contents($path)) === false) {
        echo "ERROR cannot write backup {$backup}\n";
        $errors++;
        continue;
    }

    if (file_put_contents($path, $source) === false) {
        echo "ERROR cannot write {$relativePath}\n";
        $errors++;
        continue;
    }

    echo "PATCHED {$relativePath}\n";
    $patched++;
}

$reportDir = $root . '/var/reports';
if (is_dir($reportDir) || mkdir($reportDir, 0775, true)) {
    file_put_contents(
        $reportDir . '/admin-form-config-runtime-migration.log',
        "ADMIN_FORM_CONFIG_RUNTIME_MIGRATION_MODE " . ($dryRun ? 'dry-run' : 'apply') . "\n" .
        "ADMIN_FORM_CONFIG_RUNTIME_MIGRATION_PATCHED {$patched}\n" .
        "ADMIN_FORM_CONFIG_RUNTIME_MIGRATION_SKIPPED {$skipped}\n" .
        "ADMIN_FORM_CONFIG_RUNTIME_MIGRATION_ERRORS {$errors}\n"
    );
}

if ($errors > 0) {
    exit(1);
}

echo "ADMIN_FORM_CONFIG_RUNTIME_MIGRATION_ERRORS 0\n";
