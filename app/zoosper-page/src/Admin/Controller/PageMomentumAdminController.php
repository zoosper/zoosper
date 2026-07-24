<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Controller;

use Zoosper\Page\Admin\PageAdminDashboardIndicatorProvider;
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
        private readonly PageAdminDashboardIndicatorProvider $indicatorProvider = new PageAdminDashboardIndicatorProvider(),
    ) {
    }

    public function index(): string
    {
        $statusCards = $this->renderCards($this->statusProvider->items(), 'label');
        $readinessCards = $this->renderCards($this->launchReadinessProvider->sections(), 'heading');
        $indicatorCards = $this->renderCards($this->indicatorProvider->indicators(), 'label');

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
    <section>
        <h3>Dashboard indicators</h3>
        <div class="zoosper-admin-grid zoosper-admin-grid--two">
            {$indicatorCards}
        </div>
    </section>
    <footer class="zoosper-admin-card__footer">
        <p>Route: <code>/admin/page-momentum</code> · Permission: <code>page.manage</code> · Mode: read-only</p>
    </footer>
</section>
HTML;
    }

    /**
     * @param list<array<string, string>> $items
     */
    private function renderCards(array $items, string $headingKey): string
    {
        $cards = '';
        foreach ($items as $item) {
            $cards .= sprintf(
                '<article class="zoosper-admin-card zoosper-admin-card--nested"><h3>%s</h3><p><strong>%s</strong></p><p>%s</p></article>',
                htmlspecialchars($item[$headingKey] ?? '', ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($item['status'] ?? '', ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($item['detail'] ?? '', ENT_QUOTES, 'UTF-8'),
            );
        }

        return $cards;
    }
}
