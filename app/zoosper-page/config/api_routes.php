<?php

declare(strict_types=1);

use Zoosper\Page\Controller\SitemapRobotsController;

return [
    ['method' => 'GET', 'path' => '/sitemap.xml', 'controller' => SitemapRobotsController::class, 'action' => 'sitemap', 'public' => true],
    ['method' => 'GET', 'path' => '/robots.txt', 'controller' => SitemapRobotsController::class, 'action' => 'robots', 'public' => true],
];
