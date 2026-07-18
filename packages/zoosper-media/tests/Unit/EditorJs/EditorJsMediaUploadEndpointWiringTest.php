<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\EditorJs;

test('media module declares Editor.js async upload route', function () {
    $root = dirname(__DIR__, 5);
    $routes = (string) file_get_contents($root . '/packages/zoosper-media/config/admin_routes.php');

    expect($routes)->toContain('/admin/media/editorjs/upload');
    expect($routes)->toContain('MediaEditorJsUploadController::class');
    expect($routes)->toContain("'permission' => 'media.manage'");
});

test('editorjs upload controller uses image field and response factory', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/packages/zoosper-media/src/Controller/MediaEditorJsUploadController.php');

    expect($source)->toContain("\$_FILES['image']");
    expect($source)->toContain('EditorJsImageUploadResponseFactory');
    expect($source)->toContain('Response::json');
    expect($source)->toContain('$this->responses->success');
});

test('media module registers editorjs response service and controller factory', function () {
    $root = dirname(__DIR__, 5);
    $services = (string) file_get_contents($root . '/packages/zoosper-media/config/services.php');
    $controllers = (string) file_get_contents($root . '/packages/zoosper-media/config/controllers.php');

    expect($services)->toContain('EditorJsImageUploadResponseFactory::class');
    expect($controllers)->toContain('MediaEditorJsUploadController::class');
});
