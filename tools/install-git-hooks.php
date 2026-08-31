<?php

declare(strict_types=1);

/**
 * Phase 1.85 Git hooks installer.
 *
 * Installs a pre-push hook that runs the strict quality gate so regressions are
 * blocked before they leave a developer's machine. Dry-run by default; pass
 * --apply to write the hook.
 *
 * This tool is intentionally durable (see config/durable-tools.php).
 */

$root = dirname(__DIR__);
$apply = in_array('--apply', $argv, true);
$mode = $apply ? 'apply' : 'dry-run';

$hooksDir = $root . '/.git/hooks';
$hookPath = $hooksDir . '/pre-push';

$hookContents = <<<'HOOK'
#!/bin/sh
# Zoosper strict quality gate pre-push hook (installed by tools/install-git-hooks.php).
# Blocks pushes when the strict gate or the test suite fails.

PHP_BIN="${PHP_BIN:-php8.5}"

echo "[pre-push] Running strict quality gate..."
"$PHP_BIN" tools/gate.php --strict || {
    echo "[pre-push] Quality gate failed. Push aborted."
    exit 1
}

if [ -x vendor/bin/pest ]; then
    echo "[pre-push] Running Pest..."
    vendor/bin/pest || {
        echo "[pre-push] Tests failed. Push aborted."
        exit 1
    }
fi

echo "[pre-push] All checks passed."
exit 0
HOOK;

echo "## Git Hooks Installer\n\n";
echo 'Generated: ' . gmdate('c') . "\n";
echo "Mode: {$mode}\n";
echo "Target: .git/hooks/pre-push\n\n";

if (!is_dir($root . '/.git')) {
    echo "No .git directory found; this does not look like a Git working tree.\n";
    echo "Skipping hook installation.\n";
    exit(0);
}

$existsAlready = is_file($hookPath);

if (!$apply) {
    echo $existsAlready
        ? "A pre-push hook already exists; --apply will overwrite it with the strict gate hook.\n"
        : "Would create a new pre-push hook that runs the strict gate and Pest.\n";
    echo "\nDry-run only. Re-run with --apply to install.\n";
    exit(0);
}

if (!is_dir($hooksDir) && !mkdir($hooksDir, 0775, true) && !is_dir($hooksDir)) {
    fwrite(STDERR, "Unable to create hooks directory: {$hooksDir}\n");
    exit(1);
}

if ($existsAlready) {
    @copy($hookPath, $hookPath . '.bak-' . gmdate('Ymd-His'));
    echo "Existing pre-push hook backed up.\n";
}

$written = file_put_contents($hookPath, $hookContents);
if ($written === false) {
    fwrite(STDERR, "Failed to write pre-push hook.\n");
    exit(1);
}
@chmod($hookPath, 0755);

echo "Installed strict-gate pre-push hook.\n";
echo "Override the PHP binary with: PHP_BIN=php ...\n";
exit(0);



