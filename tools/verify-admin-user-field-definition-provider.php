<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper AdminUser field definition provider verification\n";
print "=======================================================\n\n";

$factory = new \Zoosper\Auth\Entity\Save\AdminUserFieldRegistryFactory([
    new class implements \Zoosper\Core\Entity\Save\FieldDefinitionProviderInterface {
        public function definitions(): iterable
        {
            return [\Zoosper\Core\Entity\Save\FieldDefinition::extension('vendor_module', 'vendor_note', 'Vendor note')];
        }
    },
]);

$registry = $factory->create();
$data = (new \Zoosper\Core\Entity\Save\EntityDataObject())
    ->setData('name', 'Damu')
    ->setData('email', 'damu@example.test')
    ->setData('status', 'active')
    ->setData('locale', 'en_AU')
    ->setData('password', 'secret')
    ->setData('role_ids', [1, 2])
    ->setData('csrf_token', 'token')
    ->setData('vendor_note', 'hello')
    ->setData('rogue_column', 'must_not_persist');

$coreData = $registry->coreColumnData($data);
$extensionData = $registry->extensionData($data);
$writeMap = $registry->coreColumnWriteMap();

$checks = [
    'FieldDefinitionProviderInterface exists' => interface_exists(\Zoosper\Core\Entity\Save\FieldDefinitionProviderInterface::class),
    'AdminUserFieldDefinitionProvider exists' => class_exists(\Zoosper\Auth\Entity\Save\AdminUserFieldDefinitionProvider::class),
    'AdminUserFieldRegistryFactory exists' => class_exists(\Zoosper\Auth\Entity\Save\AdminUserFieldRegistryFactory::class),
    'AdminUser write map includes name' => ($writeMap['name'] ?? null) === 'name',
    'AdminUser write map includes email' => ($writeMap['email'] ?? null) === 'email',
    'AdminUser write map includes status' => ($writeMap['status'] ?? null) === 'status',
    'AdminUser write map includes locale' => ($writeMap['locale'] ?? null) === 'locale',
    'handler fields are excluded from core write data' => !isset($coreData['password']) && !isset($coreData['role_ids']),
    'virtual fields are excluded from core write data' => !isset($coreData['csrf_token']),
    'rogue fields are excluded from core write data' => !isset($coreData['rogue_column']),
    'core write data includes locale' => ($coreData['locale'] ?? null) === 'en_AU',
    'third-party extension field is separated' => ($extensionData['vendor_module']['vendor_note'] ?? null) === 'hello',
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nAdminUser core write columns:\n";
foreach ($writeMap as $field => $column) {
    print '- ' . $field . ' => ' . $column . PHP_EOL;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
