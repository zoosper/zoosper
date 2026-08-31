<?php

declare(strict_types=1);

it('keeps both Theme Admin views semantic secure and contract-compatible', function (): void {
    $root = dirname(__DIR__, 5);
    $paths = [
        $root . '/app/zoosper-theme/resources/views/admin/themes/index.php',
        $root . '/themes/admin/default/templates/modules/zoosper-theme/admin/themes/index.php',
    ];

    foreach ($paths as $path) {
        $view = (string) file_get_contents($path);
        expect($view)->toContain('class="page-header"')
            ->toContain('class="admin-table-scroll"')
            ->toContain('class="admin-card-grid"')
            ->toContain('class="admin-empty-state" role="status"')
            ->toContain('scope="col"')
            ->toContain('method="post" action="<?= $e($assignUrl) ?>"')
            ->toContain('name="_csrf_token" value="<?= $e($csrfToken) ?>"')
            ->toContain('name="site_id" value="<?= $e($site->id) ?>"')
            ->toContain('name="theme_code"')
            ->not->toContain('action="/admin/themes/assign"')
            ->not->toMatch('/\son[a-z]+\s*=/i')
            ->not->toMatch('/\sstyle\s*=/i')
            ->not->toContain('<script');
    }
});










