<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$target = $root . '/app/zoosper-admin/src/Form/AdminFormConfigAggregator.php';
$relativeTarget = 'app/zoosper-admin/src/Form/AdminFormConfigAggregator.php';
$apply = in_array('--apply', $argv, true);
$dryRun = in_array('--dry-run', $argv, true) || !$apply;
$errors = 0;
$changes = [];

if (!is_file($target)) {
    fwrite(STDERR, "Missing target: {$relativeTarget}\n");
    exit(1);
}

$source = (string) file_get_contents($target);
$original = $source;

if (str_contains($source, 'PHASE_140QR_ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED')) {
    echo "OK already patched {$relativeTarget}\n";
    exit(0);
}

if (!str_contains($source, 'require')) {
    fwrite(STDERR, "Refusing to patch: no require-style config loading found in {$relativeTarget}\n");
    exit(1);
}

if (!str_contains($source, 'AdminConfigLayeredFileLoader')) {
    $source = preg_replace(
        '/(namespace\s+[^;]+;\s*)/s',
        "$1\nuse Zoosper\\Admin\\Form\\AdminConfigLayeredFileLoader;\n",
        $source,
        1
    ) ?? $source;
    $changes[] = 'added AdminConfigLayeredFileLoader use statement';
}

$helper = <<<'PHP'

    /**
     * PHASE_140QR_ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED
     * Load an admin form config file through the proven layered runtime bridge.
     *
     * @return array<string, mixed>
     */
    private function loadLayeredAdminFormConfigFile(string $source, string $path): array
    {
        return (new AdminConfigLayeredFileLoader())->load([$source => $path]);
    }
PHP;

$replaceCount = 0;
$patterns = [
    '/(\$[A-Za-z_][A-Za-z0-9_]*\s*=\s*)require\s+(\$[A-Za-z_][A-Za-z0-9_]*);/' => '$1$this->loadLayeredAdminFormConfigFile(\'admin-form-config:\' . basename((string) $2), (string) $2);',
    '/(\$[A-Za-z_][A-Za-z0-9_]*\s*=\s*)require\s*\(\s*(\$[A-Za-z_][A-Za-z0-9_]*)\s*\)\s*;/' => '$1$this->loadLayeredAdminFormConfigFile(\'admin-form-config:\' . basename((string) $2), (string) $2);',
];

foreach ($patterns as $pattern => $replacement) {
    $source = preg_replace($pattern, $replacement, $source, -1, $count) ?? $source;
    $replaceCount += $count;
}

if ($replaceCount === 0) {
    fwrite(STDERR, "Refusing to patch: no safe require-assignment pattern was recognised in {$relativeTarget}\n");
    exit(1);
}

$changes[] = "replaced {$replaceCount} require-assignment config load(s)";

$lastBrace = strrpos($source, '}');
if ($lastBrace === false) {
    fwrite(STDERR, "Refusing to patch: could not locate final class closing brace in {$relativeTarget}\n");
    exit(1);
}

$source = substr($source, 0, $lastBrace) . $helper . "\n" . substr($source, $lastBrace);
$changes[] = 'added loadLayeredAdminFormConfigFile helper';

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$mode = $dryRun ? 'dry-run' : 'apply';
$report = [
    'ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED_PATCH_MODE ' . $mode,
    'ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED_PATCH_CHANGES ' . count($changes),
    'ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED_PATCH_ERRORS ' . $errors,
];

foreach ($changes as $change) {
    $report[] = 'CHANGE ' . $change;
}

file_put_contents($reportDir . '/admin-form-config-aggregator-layered-patch.log', implode("\n", $report) . "\n");

if ($dryRun) {
    echo "DRY-RUN would patch {$relativeTarget}\n";
    foreach ($changes as $change) {
        echo "- {$change}\n";
    }
    echo "ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED_PATCH_ERRORS 0\n";
    exit(0);
}

$backup = $target . '.phase140qr.bak';
if (!is_file($backup) && file_put_contents($backup, $original) === false) {
    fwrite(STDERR, "Could not write backup: {$backup}\n");
    exit(1);
}

if (file_put_contents($target, $source) === false) {
    fwrite(STDERR, "Could not write patched target: {$relativeTarget}\n");
    exit(1);
}

echo "PATCHED {$relativeTarget}\n";
foreach ($changes as $change) {
    echo "- {$change}\n";
}
echo "ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED_PATCH_ERRORS 0\n";
