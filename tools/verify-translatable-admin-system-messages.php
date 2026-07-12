<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controller = (string) file_get_contents($basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php');

print "Zoosper translatable admin system message verification\n";
print "======================================================\n\n";

$translator = new \Zoosper\Core\I18n\IdentityTranslator();

$checks = [
    'TranslatorInterface exists' => interface_exists(\Zoosper\Core\I18n\TranslatorInterface::class),
    'IdentityTranslator exists' => class_exists(\Zoosper\Core\I18n\IdentityTranslator::class),
    'IdentityTranslator implements TranslatorInterface' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'IdentityTranslator returns original message' => $translator->translate('Page saved successfully.') === 'Page saved successfully.',
    'IdentityTranslator replaces placeholders' => $translator->translate('Hello {name}', ['name' => 'Zoosper']) === 'Hello Zoosper',
    'PageAdminController imports TranslatorInterface' => str_contains($controller, 'TranslatorInterface'),
    'PageAdminController imports IdentityTranslator' => str_contains($controller, 'IdentityTranslator'),
    'PageAdminController accepts translator dependency' => str_contains($controller, '?TranslatorInterface $translator'),
    'PageAdminController has t helper' => str_contains($controller, 'private function t(string $message'),
    'CSRF flash message is translated' => str_contains($controller, 'error($this->t(\'Unable to save page. Invalid security token.\')'),
    'create success message is translated' => str_contains($controller, 'success($this->t(\'Page created successfully.\')'),
    'save success message is translated' => str_contains($controller, 'success($this->t(\'Page saved successfully.\')'),
    'publish status messages are translated' => str_contains($controller, '? $this->t(\'Page published successfully.\')'),
    'invalid token page title is translated' => str_contains($controller, 'html($this->t(\'Invalid token\')'),
    'not found page title is translated' => str_contains($controller, 'html($this->t(\'Page not found\')'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
