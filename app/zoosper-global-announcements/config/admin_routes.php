<?php

declare(strict_types=1);

use Zoosper\GlobalAnnouncements\Controller\AnnouncementAdminController;

return [
    [
        'method' => 'GET',
        'path' => '/admin/announcements',
        'controller' => AnnouncementAdminController::class,
        'action' => 'index',
        'permission' => 'settings.manage',
    ],
    [
        'method' => 'POST',
        'path' => '/admin/announcements/save',
        'controller' => AnnouncementAdminController::class,
        'action' => 'save',
        'permission' => 'settings.manage',
    ],
    [
        'method' => 'POST',
        'path' => '/admin/announcements/publish',
        'controller' => AnnouncementAdminController::class,
        'action' => 'publish',
        'permission' => 'settings.manage',
    ],
    [
        'method' => 'POST',
        'path' => '/admin/announcements/unpublish',
        'controller' => AnnouncementAdminController::class,
        'action' => 'unpublish',
        'permission' => 'settings.manage',
    ],
    [
        'method' => 'POST',
        'path' => '/admin/announcements/archive',
        'controller' => AnnouncementAdminController::class,
        'action' => 'archive',
        'permission' => 'settings.manage',
    ],
    [
        'method' => 'GET',
        'path' => '/admin/announcements/active',
        'controller' => AnnouncementAdminController::class,
        'action' => 'active',
        'permission' => 'admin.access',
    ],
    [
        'method' => 'POST',
        'path' => '/admin/announcements/acknowledge',
        'controller' => AnnouncementAdminController::class,
        'action' => 'acknowledge',
        'permission' => 'admin.access',
    ],
];










