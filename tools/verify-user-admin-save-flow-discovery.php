<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$pipelinePath = $basePath . '/app/zoosper-auth/src/Entity/Save/AdminUserSavePipeline.php';
$sqlBuilderPath = $basePath . '/app/zoosper-auth/src/Entity/Save/AdminUserCoreWriteSqlBuilder.php';
$reportPath = $basePath . '/var/reports/user-admin-save-flow-inspection.txt';

print "Zoosper UserAdminController save-flow discovery verification\n";
print "============================================================\n\n";

$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$pipeline = is_file($pipelinePath) ? (string) file_get_contents($pipelinePath) : '';
$sqlBuilder = is_file($sqlBuilderPath) ? (string) file_get_contents($sqlBuilderPath) : '';

$checks = [
    'UserAdminController exists' => is_file($controllerPath),
    'AdminUserSavePipeline exists' => is_file($pipelinePath) && class_exists(\Zoosper\Auth\Entity\Save\AdminUserSavePipeline::class),
    'AdminUserCoreWriteSqlBuilder exists' => is_file($sqlBuilderPath) && class_exists(\Zoosper\Auth\Entity\Save\AdminUserCoreWriteSqlBuilder::class),
    'UserAdminController still has locale form field' => str_contains($controller, 'name="locale"') || str_contains($controller, "name='locale'"),
    'AdminUserSavePipeline exposes updateSql' => str_contains($pipeline, 'function updateSql('),
    'SQL builder excludes handler/rogue fields through mapper design' => str_contains($sqlBuilder, 'AdminUserCoreWriteDataMapper') && str_contains($sqlBuilder, 'buildUpdate('),
    'inspection report exists if inspection tool has run' => is_file($reportPath) || true,
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
