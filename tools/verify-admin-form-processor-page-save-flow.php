<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controller = (string) file_get_contents($basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php');

print "Zoosper admin form processor page save flow verification\n";
print "=======================================================\n\n";

$registry = new \Zoosper\Admin\Form\AdminFormProcessorRegistry();
$registry->add(new class implements \Zoosper\Admin\Form\AdminFormProcessorInterface {
    public function formHandle(): string
    {
        return 'page.form';
    }

    public function process(array $form, array $context = []): \Zoosper\Admin\Form\AdminFormProcessingResult
    {
        if (($form['processor_probe'] ?? '') !== 'ok') {
            return \Zoosper\Admin\Form\AdminFormProcessingResult::failure(['Processor probe failed.']);
        }

        return \Zoosper\Admin\Form\AdminFormProcessingResult::success([
            'action' => $context['action'] ?? null,
        ]);
    }
});
$success = $registry->process('page.form', ['processor_probe' => 'ok'], ['action' => 'create']);
$failure = $registry->process('page.form', [], ['action' => 'update']);

$createProcessorPos = strpos($controller, "processPageForm('create'");
$createSavePos = strpos($controller, '$id = $this->pages->create(');
$updateProcessorPos = strpos($controller, "processPageForm('update'");
$updateSavePos = strpos($controller, '$this->pages->update(');

$checks = [
    'PageAdminController imports AdminFormProcessorConfigFactory' => str_contains($controller, 'AdminFormProcessorConfigFactory'),
    'PageAdminController imports AdminFormProcessorRegistry' => str_contains($controller, 'AdminFormProcessorRegistry'),
    'PageAdminController accepts injected processor registry' => str_contains($controller, '?AdminFormProcessorRegistry $pageFormProcessors'),
    'PageAdminController accepts injected processor config factory' => str_contains($controller, '?AdminFormProcessorConfigFactory $adminFormProcessorConfigFactory'),
    'PageAdminController has processPageForm helper' => str_contains($controller, 'private function processPageForm'),
    'PageAdminController has default processor registry helper' => str_contains($controller, 'defaultPageFormProcessorRegistry'),
    'create flow runs processors before repository create' => $createProcessorPos !== false && $createSavePos !== false && $createProcessorPos < $createSavePos,
    'update flow runs processors before repository update' => $updateProcessorPos !== false && $updateSavePos !== false && $updateProcessorPos < $updateSavePos,
    'processor context includes action' => str_contains($controller, "'action' => $action"),
    'processor context includes page' => str_contains($controller, "'page' => $page"),
    'processor context includes user' => str_contains($controller, "'user' => $user"),
    'processor errors return form response' => str_contains($controller, '$processorError !== null') && str_contains($controller, 'processor_save_failed'),
    'processor registry success path works' => $success->valid && ($success->payload['action'] ?? null) === 'create',
    'processor registry failure path works' => !$failure->valid && $failure->errors !== [],
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
