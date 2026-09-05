<?php

declare(strict_types=1);

use Zoosper\Core\Routing\ModuleRouteDefinition;
use Zoosper\Media\Controller\MediaEditorJsLibraryController;

it('owns a permission-protected Editor.js Media library route', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/packages/zoosper-media/config/admin_routes.php';
    $route = null;
    foreach ($routes as $candidate) {
        if (($candidate['method'] ?? null) === 'GET' && ($candidate['path'] ?? null) === '/admin/media/editorjs/library') {
            $route = $candidate;
            break;
        }
    }

    expect($route)->not->toBeNull()
        ->and($route['controller'])->toBe(MediaEditorJsLibraryController::class)
        ->and($route['action'])->toBe('index')
        ->and(ModuleRouteDefinition::normalisePermissions($route['permission'] ?? null))
        ->toBe(['media.manage', 'page.manage']);
});

it('keeps the picker response browser-safe and out of Editor runtime configuration', function (): void {
    $root = dirname(__DIR__, 5);
    $responder = (string) file_get_contents($root . '/packages/zoosper-media/src/EditorJs/MediaPickerResponder.php');
    $repository = (string) file_get_contents($root . '/packages/zoosper-media/src/EditorJs/MediaPickerReadRepository.php');
    $config = (string) file_get_contents($root . '/packages/zoosper-media/src/EditorJs/EditorJsImageToolConfig.php');

    expect($responder)->toContain("'thumbnail_url'")
        ->toContain("'url'")
        ->not->toContain("'storage_path'")
        ->not->toContain("'created_by'")
        ->not->toContain("'uuid'")
        ->and($repository)->toContain("status = 'active'")
        ->toContain("mime_type LIKE :mime_prefix")
        ->toContain('public_path IS NOT NULL')
        ->and($config)->not->toContain('/admin/media/editorjs/library');
});
