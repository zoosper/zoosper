<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Controller;

use Zoosper\Page\Admin\PageAdminLaunchReadinessProvider;
use Zoosper\Page\Admin\PageMomentumStatusProvider;

/**
 * Read-only admin momentum and launch-readiness dashboard controller.
 */
final class PageMomentumAdminController
{
    public function __construct(
        private readonly PageMomentumStatusProvider $statusProvider = new PageMomentumStatusProvider(),
        private readonly PageAdminLaunchReadinessProvider $launchReadinessProvider = new PageAdminLaunchReadinessProvider(),
    ) {
    }

    public function index(): string
    {
        $statusCards = '';
        foreach ($this->statusProvider->items() as $item) {
            $statusCards .= sprintf(
                '<article class="zoosper-admin-card zoosper-admin-card--nested"><h3>%s</h3><p><strong>%s</strong></p><p>%s</p></article>',
                htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($item['detail'], ENT_QUOTES, 'UTF-8'),
            );
        }

        $readinessCards = '';
        foreach ($this->launchReadinessProvider->sections() as $section) {
            $readinessCards .= sprintf(
                '<article class="zoosper-admin-card zoosper-admin-card--nested"><h3>%s</h3><p><strong>%s</strong></p><p>%s</p></article>',
                htmlspecialchars($section['heading'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($section['status'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($section['detail'], ENT_QUOTES, 'UTF-8'),
            );
        }

        return <<<HTML
<section class="zoosper-admin-card zoosper-page-momentum">
    <header class="zoosper-admin-card__header">
        <h2>Page momentum</h2>
        <p>Live launch-readiness status for visible page/admin improvements.</p>
    </header>
    <section>
        <h3>Live status</h3>
        <div class="zoosper-admin-grid zoosper-admin-grid--two">
            {$statusCards}
        </div>
    </section>
    <section>
        <h3>Page Admin launch-readiness dashboard</h3>
        <div class="zoosper-admin-grid zoosper-admin-grid--two">
            {$readinessCards}
        </div>
    </section>
    <footer class="zoosper-admin-card__footer">
        <p>Route: <code>/admin/page-momentum</code> · Permission: <code>page.manage</code> · Mode: read-only</p>
    </footer>
</section>
HTML;
    }
}
