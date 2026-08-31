<?php

declare(strict_types=1);

/**
 * Verify module-owned service providers and critical services.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$pdo = (new \Zoosper\Database\ConnectionFactory($config, $basePath))->create();
$modules = new \Zoosper\Core\Module\ModuleRegistry($basePath);
$logManager = new \Zoosper\Logger\Manager\LogManager($config, $basePath);
$errorHandler = new \Zoosper\Core\Error\ErrorHandler($logManager->exceptions());

$services = new \Zoosper\Core\Container\ServiceContainer();
$services->set(\Zoosper\Core\Config\ConfigRepository::class, $config);
$services->set(\Zoosper\Core\Module\ModuleRegistry::class, $modules);
$services->set(PDO::class, $pdo);
$services->set(\Zoosper\Logger\Manager\LogManager::class, $logManager);
$services->set(\Zoosper\Core\Error\ErrorHandler::class, $errorHandler);
$services->set('logger.default', $logManager->default());
$services->set('logger.exception', $logManager->exceptions());

(new \Zoosper\Logger\Module\ModuleLoggerProviderLoader($modules, $logManager, $services))->register();
(new \Zoosper\Core\Container\ServiceProviderLoader($modules, $services))->register();

$critical = [
    \Zoosper\Core\Http\JsonResponder::class,
    \Zoosper\Core\View\TemplateViewContextProvider::class,
    \Zoosper\Auth\Service\AuthService::class,
    \Zoosper\Auth\Service\SessionGuard::class,
    \Zoosper\Admin\Layout\AdminLayout::class,
    \Zoosper\Admin\UI\AdminViewRenderer::class,
    \Zoosper\Theme\Template\TemplateRenderer::class,
    'theme.frontend_template_renderer',
    'theme.admin_template_renderer',
    \Zoosper\Page\Service\PageRenderer::class,
    \Zoosper\Page\Controller\PageController::class,
    \Zoosper\Mail\Transport\MailerInterface::class,
    \Zoosper\TwoFactor\Service\AdminTwoFactorResetService::class,
];

print "Zoosper module service provider verification\n";
print "============================================\n\n";
$failed = false;
foreach ($critical as $id) {
    try {
        $service = $services->get($id);
        print '- ' . $id . ': ok (' . $service::class . ')' . PHP_EOL;
    } catch (Throwable $exception) {
        $msg = $exception->getMessage();
        $prev = $exception;
        while ($prev = $prev->getPrevious()) {
            $msg .= ' -> ' . $prev->getMessage();
        }
        print '- ' . $id . ': FAIL - ' . $msg . PHP_EOL;
        $failed = true;
    }
}

print "\nRegistered service IDs: " . count($services->ids()) . PHP_EOL;
print "Result: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);



