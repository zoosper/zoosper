<?php

declare(strict_types=1);

use Zoosper\Page\Api\PageApiController;
use Zoosper\Page\Api\ContentPageController;

return [
    ['method' => 'POST', 'path' => '/api/v1/pages', 'controller' => PageApiController::class, 'action' => 'create', 'public' => true, 'stateless' => true],
    ['method' => 'PATCH', 'path' => '/api/v1/pages/{id:\d+}', 'controller' => PageApiController::class, 'action' => 'update', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/pages', 'controller' => PageApiController::class, 'action' => 'index', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/pages/{id:\d+}', 'controller' => PageApiController::class, 'action' => 'show', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/pages/{id:\d+}/publish', 'controller' => PageApiController::class, 'action' => 'publish', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/pages/{id:\d+}/unpublish', 'controller' => PageApiController::class, 'action' => 'unpublish', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/pages/{id:\d+}/revisions', 'controller' => PageApiController::class, 'action' => 'revisions', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/pages/{id:\d+}/revisions/{revisionId:\d+}/restore', 'controller' => PageApiController::class, 'action' => 'restoreRevision', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/content/page', 'controller' => ContentPageController::class, 'action' => 'show', 'public' => true, 'stateless' => true],
];
