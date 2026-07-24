<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Controller;

use Zoosper\Page\Admin\PageMomentumStatusProvider;

/**
 * Read-only admin momentum panel controller for the page module.
 */
final class PageMomentumAdminController
{
    public function __construct(
        private readonly PageMomentumStatusProvider $statusProvider = new PageMomentumStatusProvider(),
    ) {
    }

    public function index(): string
    {
        $cards = '';
        foreach ($this->statusProvider->items() as $item) {
            $cards .= sprintf(
                '<article class="zoosper-admin-card zoosper-admin-card--nested"><h3>%s</h3><p><strong>%s</strong></p><p>%s</p></article>',
                htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($item['detail'], ENT_QUOTES, 'UTF-8'),
            );
        }

        return <<<HTML
<section class="zoosper-admin-card zoosper-page-momentum">
    <header class="zoosper-admin-card__header">
        <h2>Page momentum</h2>
        <p>Live launch-readiness status for visible page/admin improvements.</p>
    </header>
    <div class="zoosper-admin-grid zoosper-admin-grid--two">
        {$cards}
    </div>
    <footer class="zoosper-admin-card__footer">
        <p>Route: <code>/admin/page-momentum</code> · Permission: <code>page.manage</code> · Mode: read-only</p>
    </footer>
</section>
HTML;
    }
}
