<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$configPath = $basePath . '/config/i18n.php';
$config = is_file($configPath) ? require $configPath : [];

print "Zoosper admin/site locale resolution verification\n";
print "=================================================\n\n";

$resolver = new \Zoosper\Core\I18n\ConfiguredLocaleResolver($config);
$adminLocale = $resolver->resolveAdminLocale();
$siteLocale = $resolver->resolveSiteLocale();
$blankResolver = new \Zoosper\Core\I18n\ConfiguredLocaleResolver([
    'default_locale' => '',
    'admin_locale' => '',
    'site_locale' => '',
    'fallback_locale' => '',
]);
$blankAdminLocale = $blankResolver->resolveAdminLocale();
$customResolver = new \Zoosper\Core\I18n\ConfiguredLocaleResolver([
    'default_locale' => 'en_AU',
    'admin_locale' => 'ne_NP',
    'site_locale' => 'mi_NZ',
    'fallback_locale' => 'en_AU',
]);
$customAdminLocale = $customResolver->resolveAdminLocale();
$customSiteLocale = $customResolver->resolveSiteLocale();

$checks = [
    'i18n config exists' => is_file($configPath),
    'i18n config has default locale' => ($config['default_locale'] ?? null) === 'en_AU',
    'i18n config has admin locale' => ($config['admin_locale'] ?? null) === 'en_AU',
    'i18n config has site locale' => ($config['site_locale'] ?? null) === 'en_AU',
    'i18n config has fallback locale' => ($config['fallback_locale'] ?? null) === 'en_AU',
    'LocaleResolution exists' => class_exists(\Zoosper\Core\I18n\LocaleResolution::class),
    'LocaleResolverInterface exists' => interface_exists(\Zoosper\Core\I18n\LocaleResolverInterface::class),
    'ConfiguredLocaleResolver exists' => class_exists(\Zoosper\Core\I18n\ConfiguredLocaleResolver::class),
    'ConfiguredLocaleResolver implements interface' => $resolver instanceof \Zoosper\Core\I18n\LocaleResolverInterface,
    'admin locale scope is admin' => $adminLocale->scope === 'admin',
    'admin active locale resolves from config' => $adminLocale->activeLocale === 'en_AU',
    'admin fallback locale resolves from config' => $adminLocale->fallbackLocale === 'en_AU',
    'site locale scope is site' => $siteLocale->scope === 'site',
    'site active locale resolves from config' => $siteLocale->activeLocale === 'en_AU',
    'blank config falls back safely' => $blankAdminLocale->activeLocale === 'en_AU' && $blankAdminLocale->fallbackLocale === 'en_AU',
    'custom admin locale is supported' => $customAdminLocale->activeLocale === 'ne_NP' && $customAdminLocale->fallbackLocale === 'en_AU',
    'custom site locale is supported' => $customSiteLocale->activeLocale === 'mi_NZ' && $customSiteLocale->fallbackLocale === 'en_AU',
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
