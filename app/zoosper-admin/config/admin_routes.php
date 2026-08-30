<?php

declare(strict_types=1);

use Zoosper\Admin\Controller\AnnouncementAdminController;
use Zoosper\Admin\Controller\AuditLogController;
use Zoosper\Admin\Controller\DashboardController;
use Zoosper\Admin\Controller\LoginController;
use Zoosper\Admin\Controller\LoginHistoryController;

return [
    ['method' => 'GET', 'path' => '/admin/login', 'controller' => LoginController::class, 'action' => 'show', 'public' => true],
    ['method' => 'POST', 'path' => '/admin/login', 'controller' => LoginController::class, 'action' => 'login', 'public' => true],
    ['method' => 'POST', 'path' => '/admin/logout', 'controller' => LoginController::class, 'action' => 'logout', 'permission' => 'admin.access'],
    ['method' => 'GET', 'path' => '/admin', 'controller' => DashboardController::class, 'action' => 'index', 'permission' => 'admin.access'],
    ['method' => 'POST', 'path' => '/admin/dashboard/preferences', 'controller' => DashboardController::class, 'action' => 'savePreferences', 'permission' => 'admin.access'],
    ['method' => 'POST', 'path' => '/admin/dashboard/preferences/reset', 'controller' => DashboardController::class, 'action' => 'resetPreferences', 'permission' => 'admin.access'],
    ['method' => 'GET', 'path' => '/admin/dashboard/role-defaults', 'controller' => DashboardController::class, 'action' => 'roleDefaults', 'permission' => 'role.manage'],
    ['method' => 'POST', 'path' => '/admin/dashboard/role-defaults', 'controller' => DashboardController::class, 'action' => 'saveRoleDefaults', 'permission' => 'role.manage'],
    ['method' => 'POST', 'path' => '/admin/dashboard/role-defaults/reset', 'controller' => DashboardController::class, 'action' => 'resetRoleDefaults', 'permission' => 'role.manage'],
    ['method' => 'GET', 'path' => '/admin/audit-log', 'controller' => AuditLogController::class, 'action' => 'index', 'permission' => 'role.manage'],
    ['method' => 'GET', 'path' => '/admin/login-history', 'controller' => LoginHistoryController::class, 'action' => 'index', 'permission' => 'role.manage'],
    ['method' => 'GET', 'path' => '/admin/announcements', 'controller' => AnnouncementAdminController::class, 'action' => 'index', 'permission' => 'settings.manage'],
    ['method' => 'POST', 'path' => '/admin/announcements/save', 'controller' => AnnouncementAdminController::class, 'action' => 'save', 'permission' => 'settings.manage'],
    ['method' => 'POST', 'path' => '/admin/announcements/publish', 'controller' => AnnouncementAdminController::class, 'action' => 'publish', 'permission' => 'settings.manage'],
    ['method' => 'POST', 'path' => '/admin/announcements/unpublish', 'controller' => AnnouncementAdminController::class, 'action' => 'unpublish', 'permission' => 'settings.manage'],
    ['method' => 'POST', 'path' => '/admin/announcements/archive', 'controller' => AnnouncementAdminController::class, 'action' => 'archive', 'permission' => 'settings.manage'],
    ['method' => 'GET', 'path' => '/admin/announcements/active', 'controller' => AnnouncementAdminController::class, 'action' => 'active', 'permission' => 'admin.access'],
    ['method' => 'POST', 'path' => '/admin/announcements/acknowledge', 'controller' => AnnouncementAdminController::class, 'action' => 'acknowledge', 'permission' => 'admin.access'],
];
