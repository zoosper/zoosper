<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Mail\Config\SmtpConfig;
use Zoosper\Mail\Log\EmailLogRepository;
use Zoosper\Mail\Transport\LoggedMailer;
use Zoosper\Mail\Transport\MailerInterface;
use Zoosper\Mail\Transport\SmtpMailer;

return [
    SmtpConfig::class => static fn (ServiceContainer $services): SmtpConfig => new SmtpConfig($services->get(ConfigRepository::class)),
    SmtpMailer::class => static fn (ServiceContainer $services): SmtpMailer => new SmtpMailer($services->get(SmtpConfig::class)),
    EmailLogRepository::class => static fn (ServiceContainer $services): EmailLogRepository => new EmailLogRepository($services->get(PDO::class)),
    LoggedMailer::class => static fn (ServiceContainer $services): LoggedMailer => new LoggedMailer(
        $services->get(SmtpMailer::class),
        $services->get(EmailLogRepository::class),
    ),
    MailerInterface::class => static fn (ServiceContainer $services): MailerInterface => $services->get(LoggedMailer::class),
];
