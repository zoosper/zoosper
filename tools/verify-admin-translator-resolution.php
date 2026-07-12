<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php';
$configPath = $basePath . '/config/i18n.php';

print "Zoosper admin translator resolution verification\n";
print "================================================\n\n";

$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$config = is_file($configPath) ? require $configPath : [];
$resolver = new \Zoosper\Core\I18n\AdminTranslatorResolver($basePath, is_array($config) ? $config : []);
$translator = $resolver->resolve();
$fallback = new \Zoosper\Core\I18n\IdentityTranslator();

$checks = [
    'i18n config exists' => is_file($configPath),
    'AdminTranslatorResolver exists' => class_exists(\Zoosper\Core\I18n\AdminTranslatorResolver::class),
    'admin resolver returns TranslatorInterface' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'admin resolver returns catalogue-backed translation' => $translator->translate('Page saved successfully.') === 'Page saved successfully.',
    'IdentityTranslator fallback exists' => $fallback instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'IdentityTranslator fallback preserves unknown message' => $fallback->translate('Unknown {name}', ['name' => 'Zoosper']) === 'Unknown Zoosper',
    'PageAdminController accepts injected TranslatorInterface' => str_contains($controller, 'private ?TranslatorInterface $translator = null'),
    'PageAdminController uses injected translator from t helper' => str_contains($controller, '($this->translator ?? $this->defaultTranslator())->translate'),
    'PageAdminController fallback no longer constructs AdminTranslatorResolver' => !str_contains($controller, 'new AdminTranslatorResolver('),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
