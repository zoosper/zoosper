<?php

declare(strict_types=1);

namespace Zoosper\Core\Asset;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Routing\Router;

/**
 * Registers the single, public `GET /asset/{module}/{path}` route that serves
 * every enabled module's declared assets directly from disk — no publish
 * step, no manual copy into public/, no separate script to keep in sync.
 * Drop a file into a module's resources/assets/ (already declared via that
 * module's config/assets.php, e.g. app/zoosper-admin/config/assets.php) and it
 * is immediately servable.
 *
 * Deliberately registered as a PLAIN router route (via Router::get(), not
 * through ModuleRouteLoader::registerAdminRoutes()), so it carries NO
 * authentication/CSRF middleware — consistent with the existing security
 * model, where module CSS/JS served from public/ has never been
 * permission-gated (a static file under a public webroot is not sensitive by
 * definition; AssetResolver's path-traversal/extension-allowlist checks are
 * the real security boundary here, not authentication).
 *
 * Extracted into its own class (rather than inlined in ApplicationFactory) so
 * the exact production wiring can be exercised directly in a test — see
 * AssetRouteRegistrarTest — without needing to boot the full application.
 */
final class AssetRouteRegistrar
{
    /**
     * The `{path:.+}` constraint (Router's custom-constraint syntax) allows
     * the path segment to contain slashes, e.g. `css/zoosper-grid.css`, which
     * the router's default `[^/]+` single-segment matching would not permit.
     */
    private const ROUTE_PATTERN = '/asset/{module}/{path:.+}';

    public static function register(Router $router, AssetController $assetController): void
    {
        $router->get(self::ROUTE_PATTERN, static function (Request $request) use ($assetController): Response {
            $module = (string) $request->routeParam('module', '');
            $path = (string) $request->routeParam('path', '');

            $requestHeaders = [];
            $ifNoneMatch = $request->header('If-None-Match');
            if ($ifNoneMatch !== null) {
                $requestHeaders['If-None-Match'] = $ifNoneMatch;
            }

            $result = $assetController->serve($module, $path, $requestHeaders);

            return Response::raw((string) $result['body'], (int) $result['status'], $result['headers']);
        });
    }
}
