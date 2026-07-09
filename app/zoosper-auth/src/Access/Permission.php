<?php
declare(strict_types=1);

namespace Zoosper\Auth\Access;
enum Permission: string
{
    case AdminAccess = 'admin.access';
    case ApiAccess = 'api.access';
    case PageView = 'page.view';
    case PageManage = 'page.manage';
    case RoleManage = 'role.manage';
    case SettingsManage = 'settings.manage';
}
