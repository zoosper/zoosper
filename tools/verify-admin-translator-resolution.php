<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controller = (string) file_get_contents($basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php');
$configPath = $basePath . '/config/i18n.php';
$config = is_file($configPath) ? require $configPath : [];

print "Zoosper admin translator resolution verification\n";
print "================================================\n\n";

$resolver = new \Zoosper\Core\I18n\TranslationResolver($basePath);
$translator = $resolver->forLocale((string) ($config['admin_locale'] ?? 'en_AU'), (string) ($config['fallback_locale'] ?? 'en_AU'));

$checks = [
    'i18n config exists' => is_file($configPath),
    'i18n config has default locale' => ($config['default_locale'] ?? null) === 'en_AU',
    'i18n config has admin locale' => ($config['admin_locale'] ?? null) === 'en_AU',
    'i18n config has fallback locale' => ($config['fallback_locale'] ?? null) === 'en_AU',
    'TranslationResolver exists' => class_exists(\Zoosper\Core\I18n\TranslationResolver::class),
    'resolver returns TranslatorInterface' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'resolver returns catalogue-backed translation' => $translator->translate('Page saved successfully.') === 'Page saved successfully.',
    'resolver keeps source fallback for unknown messages' => $translator->translate('Unknown message') === 'Unknown message',
    'resolver keeps placeholder replacement' => $translator->translate('Hello {name}', ['name' => 'Zoosper']) === 'Hello Zoosper',
    'PageAdminController imports TranslationResolver' => str_contains($controller, 'TranslationResolver'),
    'PageAdminController still imports TranslatorInterface' => str_contains($controller, 'TranslatorInterface'),
    'PageAdminController has default translator helper' => str_contains($controller, 'private function defaultTranslator(): TranslatorInterface'),
    'PageAdminController reads i18n config' => str_contains($controller, "array('i18n')"),
    'PageAdminController resolves admin locale' => str_contains($controller, "'admin_locale'"),
    'PageAdminController resolves fallback locale' => str_contains($controller, "'fallback_locale'"),
    'PageAdminController uses defaultTranslator from t helper' => str_contains($controller, '$this->defaultTranslator()'),
    'PageAdminController no longer directly creates IdentityTranslator' => !str_contains($controller, 'new IdentityTranslator()'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
