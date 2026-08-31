<?php

declare(strict_types=1);

use Zoosper\Core\Announcement\AdminAnnouncementProviderInterface;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\GlobalAnnouncements\Announcement\AdminAnnouncementRepository;

return [
    AdminAnnouncementRepository::class => static fn (ServiceContainer $services): AdminAnnouncementRepository => new AdminAnnouncementRepository($services->get(\PDO::class)),
    AdminAnnouncementProviderInterface::class => static fn (ServiceContainer $services): AdminAnnouncementProviderInterface => $services->get(AdminAnnouncementRepository::class),
];










