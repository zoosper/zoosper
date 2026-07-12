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

$manifestPosition = strpos($applicationFactory, 'ServiceProviderManifestLoader');
$controllerLoaderPosition = strpos($applicationFactory, 'ControllerProviderLoader');
$providerLoaderRunPosition = strpos($applicationFactory, 'new ServiceProviderLoader');

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
    'ApplicationFactory references ServiceProviderManifestLoader' => $manifestPosition !== false,
    'ApplicationFactory loads manifest after module services' => $providerLoaderRunPosition !== false && $manifestPosition !== false && $providerLoaderRunPosition < $manifestPosition,
    'ApplicationFactory loads manifest before controller providers' => $manifestPosition !== false && $controllerLoaderPosition !== false && $manifestPosition < $controllerLoaderPosition,
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
