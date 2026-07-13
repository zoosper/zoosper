<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper AdminUser save data pipeline verification\n";
print "=================================================\n\n";

$submitted = [
    'name' => 'Damu',
    'email' => 'damu@example.test',
    'status' => 'active',
    'locale' => 'en_AU',
    'password' => 'secret',
    'role_ids' => [1, 2],
    'csrf_token' => 'token',
    'vendor_note' => 'hello',
    'rogue_column' => 'must_not_persist',
];

$dataFactory = new \Zoosper\Auth\Entity\Save\AdminUserSaveDataFactory();
$data = $dataFactory->fromSubmitted($submitted);
$mapper = new \Zoosper\Auth\Entity\Save\AdminUserCoreWriteDataMapper();
$coreData = $mapper->map($data);
$context = (new \Zoosper\Auth\Entity\Save\AdminUserSavePipelineContextFactory())->create($submitted, 7);
$extensionData = $context->fieldRegistry()->extensionData($context->data());

$unsafeData = $dataFactory->fromSubmitted(['locale' => '../bad']);
$blankData = $dataFactory->fromSubmitted(['locale' => '']);

$checks = [
    'AdminUserSaveDataFactory exists' => class_exists(\Zoosper\Auth\Entity\Save\AdminUserSaveDataFactory::class),
    'AdminUserCoreWriteDataMapper exists' => class_exists(\Zoosper\Auth\Entity\Save\AdminUserCoreWriteDataMapper::class),
    'AdminUserSavePipelineContextFactory exists' => class_exists(\Zoosper\Auth\Entity\Save\AdminUserSavePipelineContextFactory::class),
    'submitted locale is normalised into data object' => $data->getData('locale') === 'en_AU',
    'unsafe locale becomes null' => $unsafeData->getData('locale') === null,
    'blank locale becomes null' => $blankData->getData('locale') === null,
    'core write data includes locale' => ($coreData['locale'] ?? null) === 'en_AU',
    'core write data includes declared fields' => isset($coreData['name'], $coreData['email'], $coreData['status']),
    'handler fields are excluded from core write data' => !isset($coreData['password']) && !isset($coreData['role_ids']),
    'virtual fields are excluded from core write data' => !isset($coreData['csrf_token']),
    'rogue fields are excluded from core write data' => !isset($coreData['rogue_column']),
    'save context has correct entity type' => $context->entityType() === 'admin_user',
    'save context carries entity id' => $context->entityId() === 7,
    'save context extension data remains available' => ($extensionData['vendor_module']['vendor_note'] ?? null) === 'hello',
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nCore write data sample:\n";
foreach ($coreData as $column => $value) {
    print '- ' . $column . ': ' . (is_array($value) ? json_encode($value) : (string) $value) . PHP_EOL;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
