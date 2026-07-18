<?php

declare(strict_types=1);

use Zoosper\Media\Controller\MediaAdminController;
use Zoosper\Media\Controller\MediaEditorJsUploadController;

return [
    ['method' => 'GET', 'path' => '/admin/media', 'controller' => MediaAdminController::class, 'action' => 'index', 'permission' => 'media.manage'],
    ['method' => 'GET', 'path' => '/admin/media/upload', 'controller' => MediaAdminController::class, 'action' => 'uploadForm', 'permission' => 'media.manage'],
    ['method' => 'POST', 'path' => '/admin/media/upload', 'controller' => MediaAdminController::class, 'action' => 'upload', 'permission' => 'media.manage'],
    ['method' => 'POST', 'path' => '/admin/media/editorjs/upload', 'controller' => MediaEditorJsUploadController::class, 'action' => 'upload', 'permission' => 'media.manage'],
];
