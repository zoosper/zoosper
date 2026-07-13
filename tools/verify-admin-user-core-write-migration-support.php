<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper AdminUser core write migration support verification\n";
print "==========================================================\n\n";

$submitted = [
    'name' => 'Damu',
    'email' => 'damu@example.test',
    'status' => 'active',
    'locale' => 'en_AU',
    'password' => 'secret',
    'role_ids' => [1, 2],
    'csrf_token' => 'token',
    'rogue_column' => 'must_not_persist',
];

$pipeline = new \Zoosper\Auth\Entity\Save\AdminUserSavePipeline();
$result = $pipeline->updateSql(42, $submitted);
$sql = $result['sql'];
$params = $result['params'];
$context = $pipeline->context($submitted, 42);

$checks = [
    'AdminUserCoreWriteSqlBuilder exists' => class_exists(\Zoosper\Auth\Entity\Save\AdminUserCoreWriteSqlBuilder::class),
    'AdminUserSavePipeline exists' => class_exists(\Zoosper\Auth\Entity\Save\AdminUserSavePipeline::class),
    'pipeline creates admin_user context' => $context->entityType() === 'admin_user',
    'pipeline context carries entity id' => $context->entityId() === 42,
    'update SQL targets admin_users' => str_starts_with($sql, 'UPDATE admin_users SET '),
    'update SQL includes locale column' => str_contains($sql, 'locale = :field_locale'),
    'update SQL includes declared core fields' => str_contains($sql, 'name = :field_name') && str_contains($sql, 'email = :field_email') && str_contains($sql, 'status = :field_status'),
    'update SQL excludes handler fields' => !str_contains($sql, 'password') && !str_contains($sql, 'role_ids'),
    'update SQL excludes virtual fields' => !str_contains($sql, 'csrf_token'),
    'update SQL excludes rogue fields' => !str_contains($sql, 'rogue_column'),
    'params include id and locale' => ($params['id'] ?? null) === 42 && ($params['field_locale'] ?? null) === 'en_AU',
    'params exclude rogue and handler fields' => !isset($params['field_password'], $params['field_role_ids'], $params['field_rogue_column']),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nSQL sample:\n" . $sql . PHP_EOL;
print "\nParams sample:\n" . json_encode($params, JSON_PRETTY_PRINT) . PHP_EOL;
print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
