<?php

declare(strict_types=1);

namespace Zoosper\Core\Asset;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Routing\Router;

/**
 * Registers the single, public `GET /asset/{module}/{path}` route (and its
 * HEAD counterpart — see Phase C2 note below) that serves every enabled
 * module's declared assets directly from disk — no publish step, no manual
 * copy into public/, no separate script to keep in sync. Drop a file into a
 * module's resources/assets/ (already declared via that module's own
 * config/assets.php) and it is immediately servable.
 *
 * Deliberately registered as a PLAIN router route (via Router::map(), not
 * through ModuleRouteLoader::registerAdminRoutes()), so it carries NO
 * authentication/CSRF middleware — consistent with the existing security
 * model, where module CSS/JS served from public/ has never been
 * permission-gated. AssetResolver's path-traversal/extension-allowlist checks
 * are the real security boundary here, not authentication.
 *
 * Phase C2: also registers HEAD. Traced and confirmed the Router has NEVER
 * registered a HEAD route for anything in this codebase — a HEAD request
 * (e.g. `curl -I`, uptime monitors, some CDNs' health checks) against ANY
 * route falls through to the 404 fallback, since Router::dispatch() looks up
 * `$method . ' ' . $path` and $method comes directly from the request. This
 * is a real, standards-compliance gap being fixed here for the asset route
 * specifically (fixing it for every OTHER route is a separate, broader
 * change out of scope for this asset-pipeline phase). Per RFC 9110, a HEAD
 * response MUST have the exact same headers as the equivalent GET response,
 * but MUST NOT include a body — handled by reusing AssetController::serve()'s
 * already-correct headers/status and simply discarding the body string.
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
        $handler = static function (Request $request) use ($assetController): Response {
            $module = (string) $request->routeParam('module', '');
            $path = (string) $request->routeParam('path', '');

            $requestHeaders = [];
            $ifNoneMatch = $request->header('If-None-Match');
            if ($ifNoneMatch !== null) {
                $requestHeaders['If-None-Match'] = $ifNoneMatch;
            }

            $result = $assetController->serve($module, $path, $requestHeaders);

            // RFC 9110 §9.3.2: a HEAD response carries the SAME headers/status
            // as GET would, but MUST NOT include a body.
            $body = strtoupper($request->method()) === 'HEAD' ? '' : (string) $result['body'];

            return Response::raw($body, (int) $result['status'], $result['headers']);
        };

        $router->map('GET', self::ROUTE_PATTERN, $handler);
        $router->map('HEAD', self::ROUTE_PATTERN, $handler);
    }
}










