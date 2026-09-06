<?php

declare(strict_types=1);

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Mail\Controller\EmailLogAdminController;
use Zoosper\Mail\Log\EmailLogRepository;
use Zoosper\AdminGrid\AdminCollectionGrid;

return [
    EmailLogAdminController::class => static fn (ServiceContainer $services): EmailLogAdminController => new EmailLogAdminController(
        $services->get(SessionGuard::class),
        $services->get(AdminLayout::class),
        $services->get(EmailLogRepository::class),
        $services->get(AdminUrlGenerator::class),
        $services->get(AdminCollectionGrid::class),
        $services->get(PDO::class),
    ),
];










