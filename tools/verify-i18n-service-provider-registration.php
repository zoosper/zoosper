<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$configPath = $basePath . '/config/i18n.php';
$config = is_file($configPath) ? require $configPath : [];

print "Zoosper i18n service provider registration verification\n";
print "======================================================\n\n";

$container = new class {
    /** @var array<string, callable|object> */
    public array $services = [];

    public function set(string $id, callable|object $service): void
    {
        $this->services[$id] = $service;
    }

    public function get(string $id): mixed
    {
        $service = $this->services[$id] ?? null;

        return is_callable($service) ? $service($this) : $service;
    }
};

$provider = new \Zoosper\Core\I18n\I18nServiceProvider($basePath, $config);
$provider->register($container);

$localeResolver = $container->get(\Zoosper\Core\I18n\LocaleResolverInterface::class);
$configuredLocaleResolver = $container->get(\Zoosper\Core\I18n\ConfiguredLocaleResolver::class);
$translationFileAggregator = $container->get(\Zoosper\Core\I18n\TranslationFileAggregator::class);
$translationResolver = $container->get(\Zoosper\Core\I18n\TranslationResolver::class);
$adminTranslatorResolver = $container->get(\Zoosper\Core\I18n\AdminTranslatorResolver::class);
$translator = $container->get(\Zoosper\Core\I18n\TranslatorInterface::class);

$checks = [
    'I18nServiceProvider exists' => class_exists(\Zoosper\Core\I18n\I18nServiceProvider::class),
    'LocaleResolverInterface registered' => array_key_exists(\Zoosper\Core\I18n\LocaleResolverInterface::class, $container->services),
    'ConfiguredLocaleResolver registered' => array_key_exists(\Zoosper\Core\I18n\ConfiguredLocaleResolver::class, $container->services),
    'TranslationFileAggregator registered' => array_key_exists(\Zoosper\Core\I18n\TranslationFileAggregator::class, $container->services),
    'TranslationResolver registered' => array_key_exists(\Zoosper\Core\I18n\TranslationResolver::class, $container->services),
    'AdminTranslatorResolver registered' => array_key_exists(\Zoosper\Core\I18n\AdminTranslatorResolver::class, $container->services),
    'TranslatorInterface registered' => array_key_exists(\Zoosper\Core\I18n\TranslatorInterface::class, $container->services),
    'LocaleResolverInterface resolves correctly' => $localeResolver instanceof \Zoosper\Core\I18n\LocaleResolverInterface,
    'ConfiguredLocaleResolver resolves correctly' => $configuredLocaleResolver instanceof \Zoosper\Core\I18n\ConfiguredLocaleResolver,
    'TranslationFileAggregator resolves correctly' => $translationFileAggregator instanceof \Zoosper\Core\I18n\TranslationFileAggregator,
    'TranslationResolver resolves correctly' => $translationResolver instanceof \Zoosper\Core\I18n\TranslationResolver,
    'AdminTranslatorResolver resolves correctly' => $adminTranslatorResolver instanceof \Zoosper\Core\I18n\AdminTranslatorResolver,
    'TranslatorInterface resolves correctly' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'container-backed translator resolves known message' => $translator->translate('Page saved successfully.') === 'Page saved successfully.',
    'container-backed translator preserves unknown source message' => $translator->translate('Unknown message') === 'Unknown message',
    'container-backed translator handles placeholders' => $translator->translate('Hello {name}', ['name' => 'Zoosper']) === 'Hello Zoosper',
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
