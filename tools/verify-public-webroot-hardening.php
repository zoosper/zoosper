<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/public-webroot-policy.php';

print "Zoosper public webroot hardening verification\n";
print "============================================\n\n";

$checks = [
    'config/public_webroot.php' => is_file($basePath . '/config/public_webroot.php'),
    'audit-public-webroot.php' => is_file($basePath . '/tools/audit-public-webroot.php'),
    'quarantine-public-webroot-files.php' => is_file($basePath . '/tools/quarantine-public-webroot-files.php'),
    'nginx hardening sample' => is_file($basePath . '/deploy/nginx/zoosper-public-hardening.conf'),
];

$policy = zoosper_public_policy_load($basePath);
$checks['blocked root /var/'] = in_array('/var/', $policy['blocked_roots'] ?? [], true);
$checks['blocked extension php'] = in_array('php', $policy['blocked_extensions'] ?? [], true);
$checks['allowed front controller'] = in_array('/index.php', $policy['allowed_php_files'] ?? [], true);

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
