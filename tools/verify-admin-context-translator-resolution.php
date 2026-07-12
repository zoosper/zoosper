<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php';
$configPath = $basePath . '/app/zoosper-page/config/controllers.php';
$providerPath = $basePath . '/app/zoosper-core/src/I18n/I18nServiceProvider.php';

print "Zoosper admin context translator resolution verification\n";
print "========================================================\n\n";

$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$config = is_file($configPath) ? (string) file_get_contents($configPath) : '';
$providerSource = is_file($providerPath) ? (string) file_get_contents($providerPath) : '';

$services = new \Zoosper\Core\Container\ServiceContainer();
(new \Zoosper\Core\I18n\I18nServiceProvider($basePath, ['admin_locale' => 'en_AU', 'fallback_locale' => 'en_AU']))->register($services);
$contextResolver = $services->get(\Zoosper\Core\I18n\AdminContextTranslatorResolver::class);
$user = new class {
    public ?string $locale = 'en_GB';
};

$checks = [
    'AdminContextTranslatorResolver exists' => class_exists(\Zoosper\Core\I18n\AdminContextTranslatorResolver::class),
    'I18nServiceProvider registers AdminContextTranslatorResolver' => $services->has(\Zoosper\Core\I18n\AdminContextTranslatorResolver::class) && str_contains($providerSource, 'AdminContextTranslatorResolver::class'),
    'container resolves AdminContextTranslatorResolver' => $contextResolver instanceof \Zoosper\Core\I18n\AdminContextTranslatorResolver,
    'context resolver returns TranslatorInterface' => $contextResolver instanceof \Zoosper\Core\I18n\AdminContextTranslatorResolver && $contextResolver->resolveForAdminUser($user) instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'PageAdminController imports AdminContextTranslatorResolver' => str_contains($controller, 'use Zoosper\\Core\\I18n\\AdminContextTranslatorResolver;'),
    'PageAdminController accepts AdminContextTranslatorResolver dependency' => str_contains($controller, 'private ?AdminContextTranslatorResolver $adminContextTranslatorResolver = null'),
    'PageAdminController t helper uses admin user context resolver first' => str_contains($controller, '$this->adminContextTranslatorResolver?->resolveForAdminUser($this->guard->user())'),
    'Page controller config imports AdminContextTranslatorResolver' => str_contains($config, 'use Zoosper\\Core\\I18n\\AdminContextTranslatorResolver;'),
    'Page controller config injects AdminContextTranslatorResolver' => str_contains($config, '$services->has(AdminContextTranslatorResolver::class) ? $services->get(AdminContextTranslatorResolver::class) : null'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
