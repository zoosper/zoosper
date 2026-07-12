<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$configPath = $basePath . '/config/i18n.php';
$providerPath = $basePath . '/app/zoosper-core/src/I18n/SupportedLocaleProvider.php';
$i18nProviderPath = $basePath . '/app/zoosper-core/src/I18n/I18nServiceProvider.php';

print "Zoosper supported admin locales verification\n";
print "============================================\n\n";

$config = is_file($configPath) ? require $configPath : [];
$i18nProviderSource = is_file($i18nProviderPath) ? (string) file_get_contents($i18nProviderPath) : '';
$provider = new \Zoosper\Core\I18n\SupportedLocaleProvider(is_array($config) ? $config : []);
$locales = $provider->adminLocales();

$services = new \Zoosper\Core\Container\ServiceContainer();
(new \Zoosper\Core\I18n\I18nServiceProvider($basePath, is_array($config) ? $config : []))->register($services);
$containerProvider = $services->has(\Zoosper\Core\I18n\SupportedLocaleProvider::class)
    ? $services->get(\Zoosper\Core\I18n\SupportedLocaleProvider::class)
    : null;

$checks = [
    'config/i18n.php exists' => is_file($configPath),
    'supported_admin_locales config exists' => is_array($config) && isset($config['supported_admin_locales']) && is_array($config['supported_admin_locales']),
    'SupportedLocaleProvider file exists' => is_file($providerPath),
    'SupportedLocaleProvider class exists' => class_exists(\Zoosper\Core\I18n\SupportedLocaleProvider::class),
    'SupportedLocaleProvider returns at least one locale' => $locales !== [],
    'SupportedLocaleProvider includes en_AU' => isset($locales['en_AU']),
    'SupportedLocaleProvider validates supported admin locale' => $provider->isSupportedAdminLocale('en_AU'),
    'SupportedLocaleProvider rejects unsafe locale' => !$provider->isSupportedAdminLocale('../bad'),
    'I18nServiceProvider references SupportedLocaleProvider' => str_contains($i18nProviderSource, 'SupportedLocaleProvider::class'),
    'I18nServiceProvider registers SupportedLocaleProvider in container' => $containerProvider instanceof \Zoosper\Core\I18n\SupportedLocaleProvider,
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nLocales:\n";
foreach ($locales as $code => $label) {
    print '- ' . $code . ': ' . $label . PHP_EOL;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
