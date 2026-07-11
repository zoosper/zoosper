<?php

declare(strict_types=1);

/**
 * Verify template engine adapter foundation and runtime service registration.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$checks = [
    'Zoosper\\Theme\\Template\\Engine\\TemplateEngineInterface',
    'Zoosper\\Theme\\Template\\Engine\\PhpTemplateEngine',
    'Zoosper\\Theme\\Template\\Engine\\TemplateEngineRegistry',
    'Zoosper\\Theme\\Template\\Engine\\LatteTemplateEngine',
    'Zoosper\\Theme\\Template\\TemplateRenderer',
];

print "Zoosper template engine verification\n";
print "====================================\n\n";
$failed = false;
foreach ($checks as $class) {
    $exists = class_exists($class) || interface_exists($class);
    print '- ' . $class . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$exists;
}

try {
    $config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
    $pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
    $modules = new \Zoosper\Core\Module\ModuleRegistry($basePath);
    $logManager = new \Zoosper\Core\Log\LogManager($config, $basePath);
    $errorHandler = new \Zoosper\Core\Log\ErrorHandler($logManager->exceptions());

    $services = new \Zoosper\Core\Container\ServiceContainer();
    $services->set(\Zoosper\Core\Config\ConfigRepository::class, $config);
    $services->set(\Zoosper\Core\Module\ModuleRegistry::class, $modules);
    $services->set(PDO::class, $pdo);
    $services->set(\Zoosper\Core\Log\LogManager::class, $logManager);
    $services->set(\Zoosper\Core\Log\ErrorHandler::class, $errorHandler);
    $services->set('logger.default', $logManager->default());
    $services->set('logger.exception', $logManager->exceptions());

    (new \Zoosper\Core\Log\ModuleLoggerProviderLoader($modules, $logManager, $services))->register();
    (new \Zoosper\Core\Container\ServiceProviderLoader($modules, $services))->register();

    $registry = $services->get(\Zoosper\Theme\Template\Engine\TemplateEngineRegistry::class);
    $extensions = $registry->extensions();
    $hasLatte = in_array('latte', $extensions, true);
    $hasPhp = in_array('php', $extensions, true);
    print '- runtime_registered_extensions: ' . ($hasLatte && $hasPhp ? 'ok' : 'check') . ' (' . implode(', ', $extensions) . ')' . PHP_EOL;
    $failed = $failed || !$hasPhp;
} catch (Throwable $exception) {
    print '- runtime_registered_extensions: FAIL - ' . $exception->getMessage() . PHP_EOL;
    $failed = true;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
