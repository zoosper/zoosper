<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$manifestPath = $basePath . '/config/service_providers.php';
$configPath = $basePath . '/config/i18n.php';
$config = is_file($configPath) ? require $configPath : [];
$providerClass = \Zoosper\Core\I18n\I18nServiceProvider::class;

print "Zoosper i18n service provider discovery verification\n";
print "====================================================\n\n";

$manifest = is_file($manifestPath) ? require $manifestPath : [];
$providers = [];
if (is_array($manifest)) {
    $providers = isset($manifest['providers']) && is_array($manifest['providers'])
        ? array_values(array_filter($manifest['providers'], 'is_string'))
        : array_values(array_filter($manifest, 'is_string'));
}

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

if (in_array($providerClass, $providers, true)) {
    (new $providerClass($basePath, $config))->register($container);
}

$translator = array_key_exists(\Zoosper\Core\I18n\TranslatorInterface::class, $container->services)
    ? $container->get(\Zoosper\Core\I18n\TranslatorInterface::class)
    : null;

$checks = [
    'service provider manifest exists' => is_file($manifestPath),
    'service provider manifest returns array' => is_array($manifest),
    'manifest has providers list' => is_array($providers),
    'I18nServiceProvider class exists' => class_exists($providerClass),
    'manifest includes I18nServiceProvider' => in_array($providerClass, $providers, true),
    'manifest does not duplicate I18nServiceProvider' => count(array_keys($providers, $providerClass, true)) === 1,
    'I18nServiceProvider registers TranslatorInterface from manifest' => array_key_exists(\Zoosper\Core\I18n\TranslatorInterface::class, $container->services),
    'manifest-backed TranslatorInterface resolves correctly' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'manifest-backed translator resolves known message' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface && $translator->translate('Page saved successfully.') === 'Page saved successfully.',
    'manifest-backed translator handles placeholders' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface && $translator->translate('Hello {name}', ['name' => 'Zoosper']) === 'Hello Zoosper',
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
