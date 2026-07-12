<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php';
$configPath = $basePath . '/app/zoosper-page/config/controllers.php';

print "Zoosper admin translator runtime wiring verification\n";
print "====================================================\n\n";

$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$config = is_file($configPath) ? (string) file_get_contents($configPath) : '';

$checks = [
    'PageAdminController exists' => is_file($controllerPath),
    'PageAdminController imports TranslatorInterface' => str_contains($controller, 'use Zoosper\\Core\\I18n\\TranslatorInterface;'),
    'PageAdminController accepts translator dependency' => str_contains($controller, 'private ?TranslatorInterface $translator = null'),
    'PageAdminController t helper uses injected translator path' => str_contains($controller, '($this->translator ?? $this->defaultTranslator())->translate'),
    'PageAdminController fallback is lightweight IdentityTranslator' => str_contains($controller, 'new IdentityTranslator()'),
    'PageAdminController no longer constructs AdminTranslatorResolver directly' => !str_contains($controller, 'new AdminTranslatorResolver('),
    'page controller factory imports TranslatorInterface' => str_contains($config, 'use Zoosper\\Core\\I18n\\TranslatorInterface;'),
    'page controller factory passes TranslatorInterface' => str_contains($config, '$services->has(TranslatorInterface::class) ? $services->get(TranslatorInterface::class) : null'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
