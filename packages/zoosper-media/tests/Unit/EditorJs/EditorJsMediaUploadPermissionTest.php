<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\EditorJs;

use Zoosper\Core\Routing\ModuleRouteDefinition;
use Zoosper\Media\Controller\MediaEditorJsUploadController;

test('editorjs media upload route allows page managers as well as media managers', function () {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/packages/zoosper-media/config/admin_routes.php';

    $uploadRoute = null;
    foreach ($routes as $route) {
        if (($route['method'] ?? null) === 'POST' && ($route['path'] ?? null) === '/admin/media/editorjs/upload') {
            $uploadRoute = $route;
            break;
        }
    }

    expect($uploadRoute)->not->toBeNull();
    expect($uploadRoute['controller'])->toBe(MediaEditorJsUploadController::class);
    expect(ModuleRouteDefinition::normalisePermissions($uploadRoute['permission'] ?? null))->toBe(['media.manage', 'page.manage']);
});
