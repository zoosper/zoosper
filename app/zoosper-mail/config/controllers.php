<?php

declare(strict_types=1);

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Mail\Controller\EmailLogAdminController;
use Zoosper\Mail\Log\EmailLogRepository;

return [
    EmailLogAdminController::class => static fn (ServiceContainer $services): EmailLogAdminController => new EmailLogAdminController(
        $services->get(SessionGuard::class),
        $services->get(AdminLayout::class),
        $services->get(EmailLogRepository::class),
    ),
];
