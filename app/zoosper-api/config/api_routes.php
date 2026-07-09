<?php

declare(strict_types=1);

use Zoosper\Api\Controller\AuthController;
use Zoosper\Api\Controller\ContentPageController;
use Zoosper\Api\Controller\HealthController;
use Zoosper\Api\Controller\HelloController;
use Zoosper\Api\Controller\MeController;

return [
    ['method' => 'GET', 'path' => '/api/v1/health', 'controller' => HealthController::class, 'action' => 'show', 'public' => true],
    ['method' => 'GET', 'path' => '/api/v1/hello', 'controller' => HelloController::class, 'action' => 'show', 'public' => true],
    ['method' => 'POST', 'path' => '/api/v1/auth/login', 'controller' => AuthController::class, 'action' => 'login', 'public' => true],
    ['method' => 'POST', 'path' => '/api/v1/auth/logout', 'controller' => AuthController::class, 'action' => 'logout'],
    ['method' => 'GET', 'path' => '/api/v1/me', 'controller' => MeController::class, 'action' => 'show'],
    ['method' => 'GET', 'path' => '/api/v1/content/page', 'controller' => ContentPageController::class, 'action' => 'show', 'public' => true],
];
