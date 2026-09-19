<?php

declare(strict_types=1);

use Zoosper\Core\Routing\ModuleRouteDefinition;
use Zoosper\Media\Controller\MediaEditorJsLibraryController;

it('keeps picker acceptance on the protected Media route and rendered Page integration', function (): void {
    $packageRoot = dirname(__DIR__, 3);
    $routes = require $packageRoot . '/config/admin_routes.php';
    $route = null;
    foreach ($routes as $candidate) {
        if (($candidate['controller'] ?? null) === MediaEditorJsLibraryController::class) {
            $route = $candidate;
            break;
        }
    }
    $feature = (string) file_get_contents($packageRoot . '/tests/Feature/MediaPickerHttpAcceptanceTest.php');
    expect($route)->not->toBeNull()
        ->and($route['method'] ?? null)->toBe('GET')
        ->and(ModuleRouteDefinition::normalisePermissions($route['permission'] ?? null))->toBe(['media.manage', 'page.manage'])
        ->and($feature)->toContain('application/json')->toContain('data-zoosper-image-tool')->toContain('zoosper-editor-bridge.js')->toContain('editor-media-picker.js');
});
