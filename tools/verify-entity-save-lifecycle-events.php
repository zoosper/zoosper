<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper entity save lifecycle events verification\n";
print "=================================================\n\n";

$registry = new \Zoosper\Core\Entity\Save\FieldDefinitionRegistry();
$registry->registerMany([
    \Zoosper\Core\Entity\Save\FieldDefinition::coreColumn('name', 'Name'),
    \Zoosper\Core\Entity\Save\FieldDefinition::extension('vendor_module', 'vendor_note', 'Vendor note'),
]);

$data = (new \Zoosper\Core\Entity\Save\EntityDataObject())
    ->setData('name', 'Damu')
    ->setData('vendor_note', 'hello');

$context = new \Zoosper\Core\Entity\Save\EntitySaveContext('admin_user', $data, $registry, 123);
$dispatcher = new \Zoosper\Core\Entity\Save\EntitySaveEventDispatcher();
$sequence = [];
$saved = false;

$dispatcher->listen(\Zoosper\Core\Entity\Save\EntitySaveLifecycle::VALIDATE_BEFORE, static function (\Zoosper\Core\Entity\Save\EntitySaveContext $context) use (&$sequence): void {
    $sequence[] = 'validate_before';
    if ($context->data()->getData('name') === '') {
        $context->addError('name', 'Name is required.');
    }
});

$dispatcher->listen(\Zoosper\Core\Entity\Save\EntitySaveLifecycle::SAVE_BEFORE, static function (\Zoosper\Core\Entity\Save\EntitySaveContext $context) use (&$sequence): void {
    $sequence[] = 'save_before';
    $context->data()->setExtensionData('vendor_module', 'processed', true);
});

$dispatcher->listen(\Zoosper\Core\Entity\Save\EntitySaveLifecycle::SAVE_AFTER, new class($sequence) implements \Zoosper\Core\Entity\Save\EntitySaveEventListenerInterface {
    /** @param list<string> $sequence */
    public function __construct(private array &$sequence)
    {
    }

    public function handle(\Zoosper\Core\Entity\Save\EntitySaveContext $context): void
    {
        $this->sequence[] = 'save_after';
    }
});

$dispatcher->listen(\Zoosper\Core\Entity\Save\EntitySaveLifecycle::COMMIT_AFTER, static function (\Zoosper\Core\Entity\Save\EntitySaveContext $context) use (&$sequence): void {
    $sequence[] = 'commit_after';
});

$runner = new \Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner($dispatcher);
$runner->run($context, static function (\Zoosper\Core\Entity\Save\EntitySaveContext $context) use (&$saved, &$sequence): void {
    $sequence[] = 'save_callback';
    $saved = true;
});

$errorContext = new \Zoosper\Core\Entity\Save\EntitySaveContext(
    'admin_user',
    (new \Zoosper\Core\Entity\Save\EntityDataObject())->setData('name', ''),
    $registry,
    124,
);
$errorSaved = false;
$runner->run($errorContext, static function () use (&$errorSaved): void {
    $errorSaved = true;
});

$checks = [
    'EntitySaveEventListenerInterface exists' => interface_exists(\Zoosper\Core\Entity\Save\EntitySaveEventListenerInterface::class),
    'EntitySaveEventDispatcherInterface exists' => interface_exists(\Zoosper\Core\Entity\Save\EntitySaveEventDispatcherInterface::class),
    'EntitySaveEventDispatcher exists' => class_exists(\Zoosper\Core\Entity\Save\EntitySaveEventDispatcher::class),
    'EntitySaveLifecycleRunner exists' => class_exists(\Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner::class),
    'listeners are registered' => count($dispatcher->listeners(\Zoosper\Core\Entity\Save\EntitySaveLifecycle::SAVE_BEFORE)) === 1,
    'save callback executed when no validation errors' => $saved === true,
    'lifecycle sequence includes before and after save events' => $sequence === ['validate_before', 'save_before', 'save_callback', 'save_after', 'commit_after'],
    'listener can mutate extension data' => ($context->data()->getExtensionData('vendor_module')['processed'] ?? false) === true,
    'validation error blocks save callback' => $errorContext->hasErrors() && $errorSaved === false,
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nLifecycle sequence:\n" . json_encode($sequence, JSON_PRETTY_PRINT) . PHP_EOL;
print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
