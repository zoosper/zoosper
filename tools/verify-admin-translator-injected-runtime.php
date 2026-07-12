<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php';
$pageControllerConfigPath = $basePath . '/app/zoosper-page/config/controllers.php';

print "Zoosper admin translator injected runtime verification\n";
print "======================================================\n\n";

$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$pageControllerConfig = is_file($pageControllerConfigPath) ? (string) file_get_contents($pageControllerConfigPath) : '';

$provider = new \Zoosper\Core\I18n\I18nServiceProvider($basePath, ['admin_locale' => 'en_AU', 'fallback_locale' => 'en_AU']);
$services = new \Zoosper\Core\Container\ServiceContainer();
$provider->register($services);
$translator = $services->get(\Zoosper\Core\I18n\TranslatorInterface::class);

$checks = [
    'TranslatorInterface is registered by container provider' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'container translator resolves known message' => $translator->translate('Page saved successfully.') === 'Page saved successfully.',
    'Page controller config imports TranslatorInterface' => str_contains($pageControllerConfig, 'use Zoosper\\Core\\I18n\\TranslatorInterface;'),
    'Page controller config injects TranslatorInterface into PageAdminController' => str_contains($pageControllerConfig, '$services->has(TranslatorInterface::class) ? $services->get(TranslatorInterface::class) : null'),
    'PageAdminController accepts TranslatorInterface dependency' => str_contains($controller, 'private ?TranslatorInterface $translator = null'),
    'PageAdminController t helper uses injected translator first' => str_contains($controller, '($this->translator ?? $this->defaultTranslator())->translate'),
    'PageAdminController imports IdentityTranslator fallback' => str_contains($controller, 'use Zoosper\\Core\\I18n\\IdentityTranslator;'),
    'PageAdminController fallback no longer constructs AdminTranslatorResolver' => !str_contains($controller, 'new AdminTranslatorResolver('),
    'PageAdminController no longer imports AdminTranslatorResolver' => !str_contains($controller, 'use Zoosper\\Core\\I18n\\AdminTranslatorResolver;'),
    'PageAdminController fallback returns IdentityTranslator' => str_contains($controller, 'return new IdentityTranslator();'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
