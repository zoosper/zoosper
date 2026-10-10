<?php

declare(strict_types=1);

it('keeps protected API responses on explicit allow-listed field maps', function (): void {
    $root = dirname(__DIR__, 5);
    $contracts = [
        $root . '/app/zoosper-auth/src/Api/RoleApiController.php' => ["'permission_ids'", "'user_ids'"],
        $root . '/app/zoosper-menu/src/Api/MenuApiController.php' => ['normaliseMenu(', 'normaliseItem('],
        $root . '/app/zoosper-page/src/Api/PageApiController.php' => ['normaliseRevision(', 'normalise(Page $page)'],
        $root . '/app/zoosper-site/src/Api/SiteApiController.php' => ['private function row('],
        $root . '/app/zoosper-theme/src/Api/ThemeApiController.php' => ["'code'=>", "'name'=>", "'version'=>"],
        $root . '/app/zoosper-url-rewrite/src/Api/UrlRewriteApiController.php' => ['private function row('],
        $root . '/packages/zoosper-media/src/Api/MediaApiController.php' => ['normaliseAsset(', 'normaliseDerivative('],
    ];

    foreach ($contracts as $file => $markers) {
        expect($file)->toBeFile();
        $source = preg_replace('/\s+/', '', (string) file_get_contents($file));
        foreach ($markers as $marker) {
            expect($source)->toContain(preg_replace('/\s+/', '', $marker));
        }
        expect($source)
            ->not->toContain("'password_hash'=>")
            ->not->toContain("'token_hash'=>")
            ->not->toContain("'storage_path'=>")
            ->not->toContain("'authorization'=>")
            ->not->toContain("'secret_ciphertext'=>")
            ->not->toContain("'recovery_codes'=>");
    }
});

it('keeps external API Grid responses schema-mapped instead of passing through payloads', function (): void {
    $root = dirname(__DIR__, 5);
    $mappers = [
        $root . '/packages/zoosper-github-issues/src/Api/GithubIssueRowMapper.php',
        $root . '/packages/zoosper-store-orders/src/Api/StoreOrderRowMapper.php',
    ];

    foreach ($mappers as $file) {
        expect($file)->toBeFile();
        $source = (string) file_get_contents($file);
        expect($source)
            ->toContain('return [')
            ->not->toContain('return $record;');
    }
});
