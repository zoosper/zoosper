<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$manifestPath = $basePath . '/config/service_providers.php';

print "Zoosper bootstrap provider manifest loader verification\n";
print "=======================================================\n\n";

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

$loader = new \Zoosper\Core\Bootstrap\ServiceProviderManifestLoader($basePath);
$loadedCount = $loader->load($container, $manifestPath);
$translator = array_key_exists(\Zoosper\Core\I18n\TranslatorInterface::class, $container->services)
    ? $container->get(\Zoosper\Core\I18n\TranslatorInterface::class)
    : null;

$checks = [
    'ServiceProviderManifestLoader exists' => class_exists(\Zoosper\Core\Bootstrap\ServiceProviderManifestLoader::class),
    'service provider manifest exists' => is_file($manifestPath),
    'loader loads at least one provider' => $loadedCount >= 1,
    'loader registers TranslatorInterface through manifest provider' => array_key_exists(\Zoosper\Core\I18n\TranslatorInterface::class, $container->services),
    'loaded TranslatorInterface resolves correctly' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'loaded translator resolves known message' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface && $translator->translate('Page saved successfully.') === 'Page saved successfully.',
    'loaded translator handles placeholders' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface && $translator->translate('Hello {name}', ['name' => 'Zoosper']) === 'Hello Zoosper',
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nLoaded providers: " . $loadedCount . PHP_EOL;
print "Result: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
