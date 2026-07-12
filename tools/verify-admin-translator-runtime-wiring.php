<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php';
$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$configPath = $basePath . '/config/i18n.php';
$config = is_file($configPath) ? require $configPath : [];

print "Zoosper admin translator runtime wiring verification\n";
print "====================================================\n\n";

$resolver = new \Zoosper\Core\I18n\AdminTranslatorResolver($basePath, $config);
$translator = $resolver->resolve();
$locale = $resolver->resolveLocale();

$checks = [
    'PageAdminController exists' => is_file($controllerPath),
    'AdminTranslatorResolver exists' => class_exists(\Zoosper\Core\I18n\AdminTranslatorResolver::class),
    'AdminTranslatorResolver returns TranslatorInterface' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'AdminTranslatorResolver resolves admin LocaleResolution' => $locale instanceof \Zoosper\Core\I18n\LocaleResolution && $locale->scope === 'admin',
    'PageAdminController imports AdminTranslatorResolver' => str_contains($controller, 'AdminTranslatorResolver'),
    'PageAdminController defaultTranslator creates AdminTranslatorResolver' => str_contains($controller, 'new AdminTranslatorResolver('),
    'PageAdminController defaultTranslator passes project root' => str_contains($controller, '$this->projectRootPath()'),
    'PageAdminController defaultTranslator passes i18n config' => str_contains($controller, '$i18nConfig'),
    'PageAdminController defaultTranslator resolves translator' => str_contains($controller, ')->resolve()'),
    'PageAdminController no longer constructs TranslationResolver directly in defaultTranslator' => !str_contains($controller, 'new TranslationResolver($this->projectRootPath())'),
    'Known translated message still resolves' => $translator->translate('Page saved successfully.') === 'Page saved successfully.',
    'Unknown message still falls back to source' => $translator->translate('Unknown message') === 'Unknown message',
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
