<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Config\Scope\ScopeConfigRepository;
use Zoosper\Core\Config\Scope\ScopeContext;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Mail\Config\SmtpConfig;
use Zoosper\Mail\Config\SmtpConfigFactory;
use Zoosper\Mail\Diagnostics\MailConfigurationInspectorFactory;
use Zoosper\Mail\Log\EmailLogRepository;
use Zoosper\Mail\Transport\LoggedMailer;
use Zoosper\Mail\Transport\MailerInterface;
use Zoosper\Mail\Transport\SmtpMailer;

return [
    ScopeConfigRepository::class => static fn (ServiceContainer $services): ScopeConfigRepository => new ScopeConfigRepository(
        $services->get(PDO::class),
    ),
    SmtpConfigFactory::class => static fn (ServiceContainer $services): SmtpConfigFactory => new SmtpConfigFactory(
        $services->get(ConfigRepository::class),
        $services->get(ScopeConfigRepository::class),
    ),
    SmtpConfig::class => static fn (ServiceContainer $services): SmtpConfig => $services
        ->get(SmtpConfigFactory::class)
        ->forDefaultScope(),
    MailConfigurationInspectorFactory::class => static fn (ServiceContainer $services): MailConfigurationInspectorFactory => new MailConfigurationInspectorFactory(
        $services->get(SmtpConfigFactory::class),
    ),
    SmtpMailer::class => static fn (ServiceContainer $services): SmtpMailer => new SmtpMailer($services->get(SmtpConfig::class)),
    EmailLogRepository::class => static fn (ServiceContainer $services): EmailLogRepository => new EmailLogRepository($services->get(PDO::class)),
    LoggedMailer::class => static fn (ServiceContainer $services): LoggedMailer => new LoggedMailer(
        $services->get(SmtpMailer::class),
        $services->get(EmailLogRepository::class),
    ),
    MailerInterface::class => static fn (ServiceContainer $services): MailerInterface => $services->get(LoggedMailer::class),
];
