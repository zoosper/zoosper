<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php';
$configPath = $basePath . '/app/zoosper-page/config/controllers.php';

print "Zoosper admin context translator resolution apply\n";
print "=================================================\n\n";

if (!is_file($controllerPath) || !is_file($configPath)) {
    fwrite(STDERR, "Missing PageAdminController or page controller config.\n");
    exit(2);
}

$controller = (string) file_get_contents($controllerPath);
$config = (string) file_get_contents($configPath);
$originalController = $controller;
$originalConfig = $config;

if (!str_contains($controller, 'use Zoosper\\Core\\I18n\\AdminContextTranslatorResolver;')) {
    $controller = str_replace(
        "use Zoosper\\Core\\I18n\\IdentityTranslator;\n",
        "use Zoosper\\Core\\I18n\\AdminContextTranslatorResolver;\nuse Zoosper\\Core\\I18n\\IdentityTranslator;\n",
        $controller,
    );
}

if (!str_contains($controller, 'private ?AdminContextTranslatorResolver $adminContextTranslatorResolver = null')) {
    $controller = str_replace(
        'private ?TranslatorInterface $translator = null,',
        "private ?TranslatorInterface \$translator = null,\n        private ?AdminContextTranslatorResolver \$adminContextTranslatorResolver = null,",
        $controller,
    );
}

$old = 'return ($this->translator ?? $this->defaultTranslator())->translate($message, $parameters);';
$new = '$translator = $this->adminContextTranslatorResolver?->resolveForAdminUser($this->guard->user())'
    . "\n            ?? \$this->translator"
    . "\n            ?? \$this->defaultTranslator();\n\n        return \$translator->translate(\$message, \$parameters);";
if (str_contains($controller, $old)) {
    $controller = str_replace($old, $new, $controller);
}

if ($controller !== $originalController) {
    backup_once($controllerPath, '.phase-1.04.bak');
    file_put_contents($controllerPath, $controller);
    print "- updated app/zoosper-admin/src/Controller/PageAdminController.php\n";
} else {
    print "- PageAdminController already appears to be wired\n";
}

if (!str_contains($config, 'use Zoosper\\Core\\I18n\\AdminContextTranslatorResolver;')) {
    $config = str_replace(
        "use Zoosper\\Core\\I18n\\TranslatorInterface;\n",
        "use Zoosper\\Core\\I18n\\AdminContextTranslatorResolver;\nuse Zoosper\\Core\\I18n\\TranslatorInterface;\n",
        $config,
    );
}

$needle = '$services->has(TranslatorInterface::class) ? $services->get(TranslatorInterface::class) : null,';
$addition = "        \$services->has(TranslatorInterface::class) ? \$services->get(TranslatorInterface::class) : null,\n        \$services->has(AdminContextTranslatorResolver::class) ? \$services->get(AdminContextTranslatorResolver::class) : null,";
if (str_contains($config, $needle) && !str_contains($config, 'AdminContextTranslatorResolver::class) ? $services->get(AdminContextTranslatorResolver::class)')) {
    $config = str_replace($needle, $addition, $config);
}

if ($config !== $originalConfig) {
    backup_once($configPath, '.phase-1.04.bak');
    file_put_contents($configPath, $config);
    print "- updated app/zoosper-page/config/controllers.php\n";
} else {
    print "- page controller config already appears to be wired\n";
}

print "\nResult: OK\n";

function backup_once(string $path, string $suffix): void
{
    $backup = $path . $suffix;
    if (!is_file($backup)) {
        copy($path, $backup);
    }
}
