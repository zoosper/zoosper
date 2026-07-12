<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$rootConfig = is_file($basePath . '/config/admin_forms.php') ? require $basePath . '/config/admin_forms.php' : [];
$config = (new \Zoosper\Admin\Form\AdminFormConfigAggregator($basePath))->aggregate($rootConfig);

print "Zoosper admin form processor foundation verification\n";
print "====================================================\n\n";

$registry = new \Zoosper\Admin\Form\AdminFormProcessorRegistry();
$registry->add(new class implements \Zoosper\Admin\Form\AdminFormProcessorInterface {
    public function formHandle(): string
    {
        return 'page.form';
    }

    public function process(array $form, array $context = []): \Zoosper\Admin\Form\AdminFormProcessingResult
    {
        return \Zoosper\Admin\Form\AdminFormProcessingResult::success([
            'sample' => $form['sample'] ?? null,
        ]);
    }
});
$registry->add(new class implements \Zoosper\Admin\Form\AdminFormProcessorInterface {
    public function formHandle(): string
    {
        return 'page.form';
    }

    public function process(array $form, array $context = []): \Zoosper\Admin\Form\AdminFormProcessingResult
    {
        return isset($form['required'])
            ? \Zoosper\Admin\Form\AdminFormProcessingResult::success()
            : \Zoosper\Admin\Form\AdminFormProcessingResult::failure(['Required sample field is missing.']);
    }
});

$success = $registry->process('page.form', ['sample' => 'ok', 'required' => 'yes']);
$failure = $registry->process('page.form', ['sample' => 'ok']);
$factoryRegistry = (new \Zoosper\Admin\Form\AdminFormProcessorConfigFactory())->create($config);

$checks = [
    'AdminFormProcessingResult exists' => class_exists(\Zoosper\Admin\Form\AdminFormProcessingResult::class),
    'AdminFormProcessorInterface exists' => interface_exists(\Zoosper\Admin\Form\AdminFormProcessorInterface::class),
    'AdminFormProcessorRegistry exists' => class_exists(\Zoosper\Admin\Form\AdminFormProcessorRegistry::class),
    'AdminFormProcessorConfigFactory exists' => class_exists(\Zoosper\Admin\Form\AdminFormProcessorConfigFactory::class),
    'aggregated config has processors key' => isset($config['processors']) && is_array($config['processors']),
    'root config has processors key' => isset($rootConfig['processors']) && is_array($rootConfig['processors']),
    'page module config has page.form processors key' => isset(($config['processors'] ?? [])['page.form']),
    'processor registry returns success payload' => $success->valid && ($success->payload['sample'] ?? null) === 'ok',
    'processor registry merges validation errors' => !$failure->valid && $failure->errors !== [],
    'processor config factory returns registry' => $factoryRegistry instanceof \Zoosper\Admin\Form\AdminFormProcessorRegistry,
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
