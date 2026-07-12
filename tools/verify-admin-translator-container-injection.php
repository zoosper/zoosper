<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$applicationFactoryPath = $basePath . '/app/zoosper-core/src/Bootstrap/ApplicationFactory.php';
$pageControllerConfigPath = $basePath . '/app/zoosper-page/config/controllers.php';
$i18nServiceProviderPath = $basePath . '/app/zoosper-core/src/I18n/I18nServiceProvider.php';

print "Zoosper admin translator container injection verification\n";
print "=========================================================\n\n";

$applicationFactory = is_file($applicationFactoryPath) ? (string) file_get_contents($applicationFactoryPath) : '';
$pageControllerConfig = is_file($pageControllerConfigPath) ? (string) file_get_contents($pageControllerConfigPath) : '';
$i18nServiceProvider = is_file($i18nServiceProviderPath) ? (string) file_get_contents($i18nServiceProviderPath) : '';

$moduleServiceLoaderPosition = strpos($applicationFactory, '(new ServiceProviderLoader');
$manifestLoaderCallPosition = strpos($applicationFactory, '(new \\Zoosper\\Core\\Bootstrap\\ServiceProviderManifestLoader');
$controllerProviderLoadPosition = strpos($applicationFactory, '(new ControllerProviderLoader');

$provider = new \Zoosper\Core\I18n\I18nServiceProvider($basePath, ['admin_locale' => 'en_AU', 'fallback_locale' => 'en_AU']);
$services = new \Zoosper\Core\Container\ServiceContainer();
$provider->register($services);
$translator = $services->get(\Zoosper\Core\I18n\TranslatorInterface::class);

$checks = [
    'I18nServiceProvider uses factory when available' => str_contains($i18nServiceProvider, "method_exists(\$container, 'factory')") && str_contains($i18nServiceProvider, '->factory($id, $factory)'),
    'container-registered TranslatorInterface resolves to translator object' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'container-registered translator resolves known message' => $translator->translate('Page saved successfully.') === 'Page saved successfully.',
    'Page controller config imports TranslatorInterface' => str_contains($pageControllerConfig, 'use Zoosper\\Core\\I18n\\TranslatorInterface;'),
    'Page controller config passes TranslatorInterface dependency' => str_contains($pageControllerConfig, '$services->has(TranslatorInterface::class) ? $services->get(TranslatorInterface::class) : null'),
    'ApplicationFactory has module service loader call' => $moduleServiceLoaderPosition !== false,
    'ApplicationFactory has manifest loader runtime call' => $manifestLoaderCallPosition !== false,
    'ApplicationFactory has controller provider runtime call' => $controllerProviderLoadPosition !== false,
    'ApplicationFactory loads manifest after module services' => $moduleServiceLoaderPosition !== false && $manifestLoaderCallPosition !== false && $moduleServiceLoaderPosition < $manifestLoaderCallPosition,
    'ApplicationFactory loads manifest before controller providers' => $manifestLoaderCallPosition !== false && $controllerProviderLoadPosition !== false && $manifestLoaderCallPosition < $controllerProviderLoadPosition,
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
