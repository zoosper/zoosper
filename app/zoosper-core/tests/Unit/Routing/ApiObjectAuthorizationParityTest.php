<?php

declare(strict_types=1);

it('keeps Site-scoped API objects inside the resolved request Site', function (): void {
    $root = dirname(__DIR__, 5);
    $controllers = [
        'Menu' => $root . '/app/zoosper-menu/src/Api/MenuApiController.php',
        'Page' => $root . '/app/zoosper-page/src/Api/PageApiController.php',
        'URL Rewrite' => $root . '/app/zoosper-url-rewrite/src/Api/UrlRewriteApiController.php',
    ];

    foreach ($controllers as $feature => $file) {
        expect($file)->toBeFile();
        $source = preg_replace('/\s+/', '', (string) file_get_contents($file));
        expect($source, $feature . ' API must resolve the request Site')
            ->toContain('siteContext()');
    }

    $menu = preg_replace('/\s+/', '', (string) file_get_contents($controllers['Menu']));
    expect($menu)
        ->toContain('privatefunctionsiteMenu(Request$request):?Menu')
        ->toContain('$menu->siteId===$request->siteContext()?->siteId');

    $page = preg_replace('/\s+/', '', (string) file_get_contents($controllers['Page']));
    expect($page)
        ->toContain('privatefunctionsitePage(Request$request):?Page')
        ->toContain('$page->siteId===$request->siteContext()?->siteId');

    $rewrite = preg_replace('/\s+/', '', (string) file_get_contents($controllers['URL Rewrite']));
    expect($rewrite)
        ->toContain('findByIdForSite(')
        ->toContain('pageForSite($site,');
});

it('keeps global management APIs explicitly permission-protected', function (): void {
    $root = dirname(__DIR__, 5);
    $contracts = [
        $root . '/app/zoosper-auth/src/Api/RoleApiController.php' => ['role.view', 'role.manage'],
        $root . '/app/zoosper-site/src/Api/SiteApiController.php' => ['settings.manage'],
        $root . '/app/zoosper-theme/src/Api/ThemeApiController.php' => ['settings.manage'],
        $root . '/packages/zoosper-media/src/Api/MediaApiController.php' => ['media.manage'],
    ];

    foreach ($contracts as $file => $permissions) {
        expect($file)->toBeFile();
        $source = (string) file_get_contents($file);
        foreach ($permissions as $permission) {
            expect($source)->toContain($permission);
        }
    }
});
