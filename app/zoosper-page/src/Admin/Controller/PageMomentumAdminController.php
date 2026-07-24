<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Controller;

/**
 * Read-only admin momentum panel controller for the page module.
 *
 * This controller is intentionally not registered by default. It exists so a
 * later wiring phase can expose a visible, read-only page momentum panel behind
 * admin routing and permission checks.
 */
final class PageMomentumAdminController
{
    public function index(): string
    {
        return <<<'HTML'
<section class="zoosper-admin-card zoosper-page-momentum">
    <header class="zoosper-admin-card__header">
        <h2>Page momentum</h2>
        <p>Launch-readiness status for visible page/admin improvements.</p>
    </header>
    <div class="zoosper-admin-grid zoosper-admin-grid--three">
        <article class="zoosper-admin-card zoosper-admin-card--nested">
            <h3>Architecture foundation</h3>
            <p>Core decoupling readiness is closed before runtime cutover.</p>
        </article>
        <article class="zoosper-admin-card zoosper-admin-card--nested">
            <h3>Rendering extension</h3>
            <p>PageRenderer report-only candidate is planned and fixture-gated.</p>
        </article>
        <article class="zoosper-admin-card zoosper-admin-card--nested">
            <h3>Visible page work</h3>
            <p>Admin page momentum panel is ready for safe route/menu wiring.</p>
        </article>
    </div>
</section>
HTML;
    }
}
