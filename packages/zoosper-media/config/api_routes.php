<?php

declare(strict_types=1);

use Zoosper\Media\Api\MediaApiController;

return [
    ['method' => 'GET', 'path' => '/api/v1/media', 'controller' => MediaApiController::class, 'action' => 'index', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/media', 'controller' => MediaApiController::class, 'action' => 'upload', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/media/{id:\d+}', 'controller' => MediaApiController::class, 'action' => 'show', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/media/{id:\d+}/derivatives', 'controller' => MediaApiController::class, 'action' => 'derivatives', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/media/{id:\d+}/archive', 'controller' => MediaApiController::class, 'action' => 'archive', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/media/{id:\d+}/restore', 'controller' => MediaApiController::class, 'action' => 'restore', 'public' => true, 'stateless' => true],
];
