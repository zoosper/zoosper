<?php

declare(strict_types=1);

use Zoosper\Auth\Layout\AdminLayoutRendererInterface;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Page\Admin\Controller\PageAdminController;

/**
 * Phase 1.41 (partial, round 3a) — proves PageAdminController's `layout` and
 * `views` dependencies are typed to the shared interfaces (already proven by
 * the two-factor and media decoupling phases), not the concrete
 * Zoosper\Admin\Layout\AdminLayout / Zoosper\Admin\UI\AdminViewRenderer
 * classes.
 *
 * This test deliberately does NOT assert "zero Zoosper\Admin\ imports" the
 * way the two-factor/media tests do — zoosper-page still legitimately
 * imports several other concrete Admin classes (AdminFormSection and the
 * admin-form runtime machinery), and zoosper/admin remains a real
 * composer.json dependency until a separate, larger phase addresses that.
 *
 * File placement: app/zoosper-page/tests/Unit/Architecture/PageAdminLayoutViewDecouplingTest.php
 * — 5 levels up to repo root, matching other per-module architecture tests.
 */
it('confirms PageAdminController depends on AdminLayoutRendererInterface, not the concrete AdminLayout', function (): void {
    $constructor = (new ReflectionClass(PageAdminController::class))->getConstructor();
    $param = null;
    foreach ($constructor->getParameters() as $parameter) {
        if ($parameter->getName() === 'layout') {
            $param = $parameter;
        }
    }

    expect($param)->not->toBeNull();
    expect((string) $param->getType())->toContain(AdminLayoutRendererInterface::class);
});

it('confirms Page Grid response depends on AdminViewRendererInterface, not the concrete AdminViewRenderer', function (): void {
    $constructor = (new ReflectionClass(\Zoosper\Page\Admin\PageAdminGridResponder::class))->getConstructor();
    $param = null;
    foreach ($constructor->getParameters() as $parameter) {
        if ($parameter->getName() === 'views') {
            $param = $parameter;
        }
    }

    expect($param)->not->toBeNull();
    expect((string) $param->getType())->toContain(AdminViewRendererInterface::class);
});

it('confirms PageAdminController.php no longer directly imports the concrete AdminLayout or AdminViewRenderer classes', function (): void {
    $basePath = dirname(__DIR__, 5);
    $file = $basePath . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php';
    $contents = (string) file_get_contents($file);

    expect($contents)->not->toContain('use Zoosper\\Admin\\Layout\\AdminLayout;');
    expect($contents)->not->toContain('use Zoosper\\Admin\\UI\\AdminViewRenderer;');
});
