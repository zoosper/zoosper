<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper entity extension data persistence verification\n";
print "=====================================================\n\n";

$registry = new \Zoosper\Core\Entity\Save\FieldDefinitionRegistry();
$registry->registerMany([
    \Zoosper\Core\Entity\Save\FieldDefinition::coreColumn('name', 'Name'),
    \Zoosper\Core\Entity\Save\FieldDefinition::extension('vendor_module', 'vendor_note', 'Vendor note'),
    \Zoosper\Core\Entity\Save\FieldDefinition::extension('security_module', 'approval_required', 'Approval required'),
    \Zoosper\Core\Entity\Save\FieldDefinition::virtual('csrf_token', 'CSRF token'),
]);

$data = (new \Zoosper\Core\Entity\Save\EntityDataObject())
    ->setData('name', 'Damu')
    ->setData('vendor_note', 'hello')
    ->setData('approval_required', true)
    ->setData('csrf_token', 'do-not-save')
    ->setData('rogue_column', 'must_not_save');

$extensionData = $registry->extensionData($data);
$coreData = $registry->coreColumnData($data);

$checks = [
    'EntityExtensionValue class exists' => class_exists(\Zoosper\Core\Entity\Extension\EntityExtensionValue::class),
    'EntityExtensionValueRepository class exists' => class_exists(\Zoosper\Core\Entity\Extension\EntityExtensionValueRepository::class),
    'EntityExtensionDataPersister class exists' => class_exists(\Zoosper\Core\Entity\Extension\EntityExtensionDataPersister::class),
    'schema SQL exists' => is_file($basePath . '/database/schema/entity_extension_values.sql'),
    'extension data includes vendor module field' => ($extensionData['vendor_module']['vendor_note'] ?? null) === 'hello',
    'extension data includes security module field' => ($extensionData['security_module']['approval_required'] ?? null) === true,
    'core data excludes extension fields' => !isset($coreData['vendor_note']) && !isset($coreData['approval_required']),
    'extension data excludes virtual field' => !isset($extensionData['csrf_token']),
    'extension data excludes rogue field' => !isset($extensionData['rogue_column']),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nExtension data sample:\n" . json_encode($extensionData, JSON_PRETTY_PRINT) . PHP_EOL;
print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
