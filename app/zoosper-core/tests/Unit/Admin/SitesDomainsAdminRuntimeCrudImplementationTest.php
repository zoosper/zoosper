<?php

declare(strict_types=1);

namespace App\zoospercore\tests\Unit\Admin;

test('sites and site domains admin controllers are module owned', function () {
    $root = dirname(__DIR__, 5);
    $sites = (string) file_get_contents($root . '/app/zoosper-site/src/Admin/Controller/SiteAdminController.php');
    $domains = (string) file_get_contents($root . '/app/zoosper-site/src/Admin/Controller/SiteDomainAdminController.php');
    expect($sites)->toContain('namespace Zoosper\Site\Admin\Controller');
    expect($domains)->toContain('namespace Zoosper\Site\Admin\Controller');
    expect($sites)->toContain('final readonly class SiteAdminController');
    expect($domains)->toContain('final readonly class SiteDomainAdminController');
});

test('sites and site domains admin routes are module owned', function () {
    $root = dirname(__DIR__, 5);
    $routes = (string) file_get_contents($root . '/app/zoosper-site/config/admin_routes.php');
    $adminRoutes = (string) file_get_contents($root . '/app/zoosper-admin/config/admin_routes.php');
    foreach (['/admin/sites', '/admin/sites/create', '/admin/sites/edit', '/admin/site-domains', '/admin/site-domains/create', '/admin/site-domains/edit'] as $route) {
        expect($routes)->toContain($route);
    }
    expect($routes)->toContain('SiteAdminController::class');
    expect($routes)->toContain('SiteDomainAdminController::class');
    expect($adminRoutes)->not->toContain('SiteAdminController::class');
    expect($adminRoutes)->not->toContain('SiteDomainAdminController::class');
});

test('site domains repository and schema are available for admin crud', function () {
    $root = dirname(__DIR__, 5);
    $repository = (string) file_get_contents($root . '/app/zoosper-site/src/Repository/SiteDomainRepository.php');
    $schema = (string) file_get_contents($root . '/app/zoosper-site/config/db_schema.php');
    expect($repository)->toContain('final readonly class SiteDomainRepository');
    expect($repository)->toContain('public function all');
    expect($repository)->toContain('public function create');
    expect($repository)->toContain('public function update');
    expect($schema)->toContain("'site_domains'");
    expect($schema)->toContain("'host'");
    expect($schema)->toContain("'is_primary'");
});

test('site admin menu entries are module owned', function () {
    $root = dirname(__DIR__, 5);
    $siteMenu = (string) file_get_contents($root . '/app/zoosper-site/config/admin_menu.php');
    $adminMenu = (string) file_get_contents($root . '/app/zoosper-admin/config/admin_menu.php');
    expect($siteMenu)->toContain("'url' => '/admin/sites'");
    expect($siteMenu)->toContain("'url' => '/admin/site-domains'");
    expect($adminMenu)->not->toContain("'label' => 'Sites'");
    expect($adminMenu)->not->toContain("'label' => 'Site Domains'");
});
