<?php

declare(strict_types=1);

use Zoosper\Audit\AuditLogRepository;
use Zoosper\Audit\AuditLogger;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Audit\Contract\LoginHistoryRecorderInterface;
use Zoosper\Audit\LoginHistoryRepository;
use Zoosper\Audit\Admin\Grid\AuditLogGridDefinition;
use Zoosper\Audit\Admin\Grid\LoginHistoryGridDefinition;
use Zoosper\Audit\Admin\Grid\OperationalGridPageBuilder;
use Zoosper\Audit\Admin\Grid\OperationalGridPageBuilderFactory;
use Zoosper\Audit\Console\PruneLogsCommand;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Core\Container\ServiceContainer;
use PDO;

return [
    OperationalGridPageBuilderFactory::class => static fn(ServiceContainer $services): OperationalGridPageBuilderFactory => new OperationalGridPageBuilderFactory($services->get(GridViewStateResolver::class)),
    OperationalGridPageBuilder::class => static fn(ServiceContainer $services): OperationalGridPageBuilder => $services->get(OperationalGridPageBuilderFactory::class)->create(),
    AuditLoggerInterface::class => static fn(ServiceContainer $services): AuditLoggerInterface => $services->get(AuditLogger::class),
    LoginHistoryRecorderInterface::class => static fn(ServiceContainer $services): LoginHistoryRecorderInterface => $services->get(LoginHistoryRepository::class),
    LoginHistoryRepository::class => static fn(ServiceContainer $services): LoginHistoryRepository => new LoginHistoryRepository($services->get(PDO::class)),
    AuditLogRepository::class => static fn(ServiceContainer $services): AuditLogRepository => new AuditLogRepository($services->get(PDO::class)),
    AuditLogger::class => static fn(ServiceContainer $services): AuditLogger => new AuditLogger($services->get(AuditLogRepository::class)),
    AuditLogGridDefinition::class => static fn(ServiceContainer $services): AuditLogGridDefinition => new AuditLogGridDefinition(),
    LoginHistoryGridDefinition::class => static fn(ServiceContainer $services): LoginHistoryGridDefinition => new LoginHistoryGridDefinition(),
    PruneLogsCommand::class => static fn(ServiceContainer $services): PruneLogsCommand => new PruneLogsCommand(
        $services->get(AuditLogRepository::class),
        $services->get(LoginHistoryRepository::class),
    ),
];
