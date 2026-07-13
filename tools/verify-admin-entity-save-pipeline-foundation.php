<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin entity save pipeline foundation verification\n";
print "=========================================================\n\n";

$registry = new \Zoosper\Core\Entity\Save\FieldDefinitionRegistry();
$registry->registerMany([
    \Zoosper\Core\Entity\Save\FieldDefinition::coreColumn('name', 'Name'),
    \Zoosper\Core\Entity\Save\FieldDefinition::coreColumn('locale', 'Admin interface locale'),
    \Zoosper\Core\Entity\Save\FieldDefinition::extension('vendor_module', 'vendor_note', 'Vendor note'),
    \Zoosper\Core\Entity\Save\FieldDefinition::virtual('csrf_token', 'CSRF token'),
]);

$data = (new \Zoosper\Core\Entity\Save\EntityDataObject())
    ->setData('name', 'Damu')
    ->setData('locale', 'en_AU')
    ->setData('csrf_token', 'abc')
    ->setData('rogue_column', 'must_not_persist')
    ->setData('vendor_note', 'hello');

$coreData = $registry->coreColumnData($data);
$extensionData = $registry->extensionData($data);
$context = new \Zoosper\Core\Entity\Save\EntitySaveContext('admin_user', $data, $registry, 1);
$context->addError('vendor_note', 'Example validation error');

$checks = [
    'EntityDataObject class exists' => class_exists(\Zoosper\Core\Entity\Save\EntityDataObject::class),
    'FieldDefinition class exists' => class_exists(\Zoosper\Core\Entity\Save\FieldDefinition::class),
    'FieldDefinitionRegistry class exists' => class_exists(\Zoosper\Core\Entity\Save\FieldDefinitionRegistry::class),
    'FieldStorageType enum exists' => enum_exists(\Zoosper\Core\Entity\Save\FieldStorageType::class),
    'EntitySaveContext class exists' => class_exists(\Zoosper\Core\Entity\Save\EntitySaveContext::class),
    'EntitySaveLifecycle class exists' => class_exists(\Zoosper\Core\Entity\Save\EntitySaveLifecycle::class),
    'setData/getData works' => $data->getData('locale') === 'en_AU',
    'core column write map includes locale' => ($registry->coreColumnWriteMap()['locale'] ?? null) === 'locale',
    'core column data excludes rogue field' => isset($coreData['locale']) && !isset($coreData['rogue_column']) && !isset($coreData['csrf_token']),
    'extension data captures module field separately' => ($extensionData['vendor_module']['vendor_note'] ?? null) === 'hello',
    'save context tracks validation errors' => $context->hasErrors() && isset($context->errors()['vendor_note']),
    'save lifecycle exposes before and after events' => \Zoosper\Core\Entity\Save\EntitySaveLifecycle::SAVE_BEFORE !== '' && \Zoosper\Core\Entity\Save\EntitySaveLifecycle::SAVE_AFTER !== '',
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nCore data sample:\n";
foreach ($coreData as $column => $value) {
    print '- ' . $column . ': ' . (string) $value . PHP_EOL;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
