<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$configPath = $basePath . '/config/i18n.php';
$config = is_file($configPath) ? require $configPath : [];

print "Zoosper admin translator locale resolver integration verification\n";
print "================================================================\n\n";

$resolver = new \Zoosper\Core\I18n\AdminTranslatorResolver($basePath, $config);
$locale = $resolver->resolveLocale();
$translator = $resolver->resolve();
$customResolver = new \Zoosper\Core\I18n\AdminTranslatorResolver($basePath, [
    'default_locale' => 'en_AU',
    'admin_locale' => 'ne_NP',
    'fallback_locale' => 'en_AU',
]);
$customLocale = $customResolver->resolveLocale();
$customTranslator = $customResolver->resolve();
$translationResolver = new \Zoosper\Core\I18n\TranslationResolver($basePath);
$translatedFromResolution = $translationResolver->forResolution($locale);

$checks = [
    'AdminTranslatorResolver exists' => class_exists(\Zoosper\Core\I18n\AdminTranslatorResolver::class),
    'TranslationResolver exists' => class_exists(\Zoosper\Core\I18n\TranslationResolver::class),
    'TranslationResolver exposes forResolution' => method_exists(\Zoosper\Core\I18n\TranslationResolver::class, 'forResolution'),
    'AdminTranslatorResolver resolve returns TranslatorInterface' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'AdminTranslatorResolver resolveLocale returns LocaleResolution' => $locale instanceof \Zoosper\Core\I18n\LocaleResolution,
    'default admin locale scope is admin' => $locale->scope === 'admin',
    'default admin locale is en_AU' => $locale->activeLocale === 'en_AU',
    'default fallback locale is en_AU' => $locale->fallbackLocale === 'en_AU',
    'default translator resolves known message' => $translator->translate('Page saved successfully.') === 'Page saved successfully.',
    'default translator preserves unknown source message' => $translator->translate('Unknown message') === 'Unknown message',
    'default translator handles placeholders' => $translator->translate('Hello {name}', ['name' => 'Zoosper']) === 'Hello Zoosper',
    'custom admin locale resolves from config' => $customLocale->activeLocale === 'ne_NP',
    'custom admin fallback locale resolves from config' => $customLocale->fallbackLocale === 'en_AU',
    'custom translator still falls back to en_AU catalogue' => $customTranslator->translate('Page saved successfully.') === 'Page saved successfully.',
    'TranslationResolver forResolution returns TranslatorInterface' => $translatedFromResolution instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'TranslationResolver forResolution resolves known message' => $translatedFromResolution->translate('Page saved successfully.') === 'Page saved successfully.',
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
