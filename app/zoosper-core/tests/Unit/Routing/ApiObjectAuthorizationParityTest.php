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
        expect($source, $feature . ' API must resolve the request Site')->toContain('siteContext()');
    }

    $menu = preg_replace('/\s+/', '', (string) file_get_contents($controllers['Menu']));
    expect($menu)->toContain('privatefunctionsiteMenu(Request$request):?Menu')->toContain('$menu->siteId===$request->siteContext()?->siteId');

    $page = preg_replace('/\s+/', '', (string) file_get_contents($controllers['Page']));
    expect($page)->toContain('privatefunctionsitePage(Request$request):?Page')->toContain('$page->siteId===$request->siteContext()?->siteId');

    $rewrite = preg_replace('/\s+/', '', (string) file_get_contents($controllers['URL Rewrite']));
    expect($rewrite)->toContain('findByIdForSite(')->toContain('pageForSite($site,');
});

it('keeps global management APIs explicitly permission-protected by route metadata', function (): void {
    $root = dirname(__DIR__, 5);
    $manifests = [
        $root . '/app/zoosper-auth/config/api_routes.php',
        $root . '/app/zoosper-site/config/api_routes.php',
        $root . '/app/zoosper-theme/config/api_routes.php',
        $root . '/packages/zoosper-media/config/api_routes.php',
    ];
    $required = [
        'Zoosper\\Auth\\Api\\RoleApiController' => ['role.view', 'role.manage'],
        'Zoosper\\Site\\Api\\SiteApiController' => ['settings.manage'],
        'Zoosper\\Theme\\Api\\ThemeApiController' => ['settings.manage'],
        'Zoosper\\Media\\Api\\MediaApiController' => ['media.manage'],
    ];
    $seen = array_fill_keys(array_keys($required), []);

    foreach ($manifests as $manifest) {
        expect($manifest)->toBeFile();
        foreach (require $manifest as $route) {
            $controller = (string) ($route['controller'] ?? '');
            if (!array_key_exists($controller, $required)) {
                continue;
            }
            $permissions = $route['permission'] ?? [];
            if (is_string($permissions)) {
                $permissions = [$permissions];
            }
            foreach ($permissions as $permission) {
                if (is_string($permission)) {
                    $seen[$controller][$permission] = true;
                }
            }
        }
    }

    foreach ($required as $controller => $permissions) {
        foreach ($permissions as $permission) {
            expect(isset($seen[$controller][$permission]), $controller . ' route metadata must retain ' . $permission)->toBeTrue();
        }
    }
});
