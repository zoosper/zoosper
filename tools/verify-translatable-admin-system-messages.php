<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php';
$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';

print "Zoosper translatable admin system message verification\n";
print "======================================================\n\n";

$identity = new \Zoosper\Core\I18n\IdentityTranslator();
$resolver = new \Zoosper\Core\I18n\AdminTranslatorResolver($basePath, ['admin_locale' => 'en_AU', 'fallback_locale' => 'en_AU']);
$translator = $resolver->resolve();

$checks = [
    'TranslatorInterface exists' => interface_exists(\Zoosper\Core\I18n\TranslatorInterface::class),
    'IdentityTranslator implements TranslatorInterface' => $identity instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'IdentityTranslator replaces placeholders' => $identity->translate('Hello {name}', ['name' => 'Zoosper']) === 'Hello Zoosper',
    'catalogue translator resolves known admin message' => $translator->translate('Page saved successfully.') === 'Page saved successfully.',
    'PageAdminController imports TranslatorInterface' => str_contains($controller, 'use Zoosper\\Core\\I18n\\TranslatorInterface;'),
    'PageAdminController accepts translator dependency' => str_contains($controller, 'private ?TranslatorInterface $translator = null'),
    'PageAdminController has t helper' => str_contains($controller, 'private function t(string $message'),
    'PageAdminController t helper translates messages' => str_contains($controller, '->translate($message, $parameters)'),
    'PageAdminController uses lightweight fallback only' => str_contains($controller, 'new IdentityTranslator()') && !str_contains($controller, 'new AdminTranslatorResolver('),
    'CSRF flash message is translated' => str_contains($controller, "\$this->t('Unable to save page. Invalid security token.')"),
    'create success message is translated' => str_contains($controller, "\$this->t('Page created successfully.')"),
    'save success message is translated' => str_contains($controller, "\$this->t('Page saved successfully.')"),
    'publish status messages are translated' => str_contains($controller, "\$this->t('Page published successfully.')") && str_contains($controller, "\$this->t('Page unpublished successfully.')"),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
