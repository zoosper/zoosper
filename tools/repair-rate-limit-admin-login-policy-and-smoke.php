<?php

declare(strict_types=1);

/**
 * Convenience repair command for Phase 1.39 admin-login report-only smoke.
 *
 * Runs the guarded admin.login policy patch first, then audits and runs the
 * report-only smoke. Existing tools remain the source of truth.
 */

$root = dirname(__DIR__);
$php = PHP_BINARY;
$commands = [
    [$php, $root . '/tools/apply-rate-limit-admin-login-policy.php', '--apply'],
    [$php, $root . '/tools/audit-rate-limit-admin-login-policy.php'],
    [$php, $root . '/tools/audit-rate-limit-admin-login-smoke.php'],
    [$php, $root . '/tools/smoke-rate-limit-admin-login-report-only.php'],
];

$failures = [];
foreach ($commands as $command) {
    $display = implode(' ', array_map('escapeshellarg', $command));
    echo '$ ' . $display . PHP_EOL;
    $process = proc_open(
        $display,
        [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
        $root,
    );

    if (! is_resource($process)) {
        $failures[] = 'Unable to start command: ' . $display;
        continue;
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($stdout !== '') {
        echo $stdout;
    }
    if ($stderr !== '') {
        fwrite(STDERR, $stderr);
    }

    if ($exitCode !== 0) {
        $failures[] = 'Command failed with exit code ' . $exitCode . ': ' . $display;
        break;
    }
}

if ($failures !== []) {
    fwrite(STDERR, PHP_EOL . 'Repair failed:' . PHP_EOL);
    foreach ($failures as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo PHP_EOL;
echo 'Admin login rate-limit policy repair and report-only smoke completed.' . PHP_EOL;
echo 'Review var/reports/rate-limit-admin-login-policy.log and var/reports/rate-limit-admin-login-smoke.log.' . PHP_EOL;
exit(0);
