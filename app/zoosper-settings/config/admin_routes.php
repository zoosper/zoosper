<?php

declare(strict_types=1);

use Zoosper\Settings\Controller\SettingsCatalogueController;

return [
    ['method' => 'GET', 'path' => '/admin/settings', 'controller' => SettingsCatalogueController::class, 'action' => 'index', 'permission' => 'settings.manage'],
    ['method' => 'POST', 'path' => '/admin/settings/save', 'controller' => SettingsCatalogueController::class, 'action' => 'save', 'permission' => 'settings.manage'],
    ['method' => 'POST', 'path' => '/admin/settings/clear', 'controller' => SettingsCatalogueController::class, 'action' => 'clear', 'permission' => 'settings.manage'],
];
