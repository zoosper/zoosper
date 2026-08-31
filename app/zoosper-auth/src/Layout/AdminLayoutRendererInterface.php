<?php

declare(strict_types=1);

namespace Zoosper\Auth\Layout;

use Zoosper\Auth\Model\AdminUser;

/**
 * Contract for rendering the admin UI shell around a content fragment.
 *
 * Phase 1.41 (dependency graph correction): lets feature modules (currently
 * two-factor's setup screen) render inside the admin layout without
 * depending on zoosper/admin's concrete AdminLayout class directly.
 *
 * HOTFIX: this interface originally lived in Zoosper\Core\Layout, but
 * Zoosper\Core\Tests\Unit\Architecture\CoreDecouplingArchitectureTest
 * correctly failed it — Core must never depend on any feature module,
 * including Auth. Since this interface's signature genuinely needs
 * AdminUser, and every consumer (zoosper-admin, zoosper-two-factor) already
 * requires zoosper/auth in its own composer.json, Auth is the correct
 * shared home for this contract — not Core. No new module dependency is
 * introduced by this relocation.
 *
 * Bound to the real AdminLayout by app/zoosper-admin/config/services.php.
 * A module requiring this interface (e.g. two-factor's setup screen)
 * inherently needs SOME UI-layout provider to be installed — there is no
 * headless way to render an HTML setup screen. What this interface removes
 * is the hard requirement that the UI provider specifically be
 * zoosper/admin's concrete class.
 */
interface AdminLayoutRendererInterface
{
    public function render(string $title, string $content, ?AdminUser $user, string $active = 'dashboard'): string;
}










