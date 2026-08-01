<?php

declare(strict_types=1);

$root = isset($argv[1]) ? realpath($argv[1]) : false;
if ($root === false || !is_dir($root . '/app/zoosper-auth/config')) {
    fwrite(STDERR, "ERROR: provide the Zoosper repository root.\n");
    exit(1);
}

$servicesPath = $root . '/app/zoosper-auth/config/services.php';
$fragmentPath = $root . '/app/zoosper-auth/config/services_auth_grid.php';

if (!is_file($servicesPath) || !is_file($fragmentPath)) {
    fwrite(STDERR, "ERROR: Auth services manifest or Grid service fragment is missing.\n");
    exit(1);
}

$source = (string) file_get_contents($servicesPath);
$marker = "...require __DIR__ . '/services_auth_grid.php',";

if (str_contains($source, $marker)) {
    echo "Auth Grid service fragment is already registered.\n";
    exit(0);
}

$matches = [];
if (preg_match_all('/^return\s*\[/m', $source, $matches, PREG_OFFSET_CAPTURE) !== 1) {
    fwrite(STDERR, "ERROR: expected exactly one top-level `return [` in Auth services manifest.\n");
    exit(1);
}

$match = $matches[0][0];
$offset = $match[1] + strlen($match[0]);
$insertion = "\n    // Auth Grid read-side services. Existing manifest entries below retain precedence.\n"
    . "    {$marker}\n";
$updated = substr($source, 0, $offset) . $insertion . substr($source, $offset);

$temp = $servicesPath . '.phase-4t.tmp';
if (file_put_contents($temp, $updated, LOCK_EX) === false) {
    fwrite(STDERR, "ERROR: failed to write temporary Auth services manifest.\n");
    exit(1);
}

$output = [];
$exitCode = 0;
exec('php8.5 -l ' . escapeshellarg($temp) . ' 2>&1', $output, $exitCode);
if ($exitCode !== 0) {
    @unlink($temp);
    fwrite(STDERR, "ERROR: updated Auth services manifest failed syntax validation:\n");
    fwrite(STDERR, implode("\n", $output) . "\n");
    exit(1);
}

if (!rename($temp, $servicesPath)) {
    @unlink($temp);
    fwrite(STDERR, "ERROR: failed to atomically activate Auth services manifest.\n");
    exit(1);
}

echo "Auth Grid service fragment registered in app/zoosper-auth/config/services.php.\n";
