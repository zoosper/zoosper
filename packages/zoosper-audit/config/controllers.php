<?php

declare(strict_types=1);

use Zoosper\Audit\Controller\AuditLogController;
use Zoosper\Audit\Controller\LoginHistoryController;
use Zoosper\Core\Container\ServiceContainer;

return [
    AuditLogController::class => static fn(ServiceContainer $services): AuditLogController => $services->autowire(AuditLogController::class),
    LoginHistoryController::class => static fn(ServiceContainer $services): LoginHistoryController => $services->autowire(LoginHistoryController::class),
];
