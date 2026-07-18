<?php

declare(strict_types=1);

namespace Zoosper\Auth\Http;

use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Middleware\RouteMiddleware;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * Central CSRF guard for stateful admin requests.
 *
 * Validates either the `_csrf_token` form field or the `X-CSRF-Token` request
 * header on state-changing admin requests. Header support is required for async
 * JavaScript uploads such as the Editor.js Image Tool.
 */
final readonly class CsrfMiddleware implements RouteMiddleware
{
    /** @var list<string> */
    private const STATEFUL_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function __construct(private CsrfTokenManager $csrf)
    {
    }

    public function process(Request $request, RouteContext $context, callable $next): Response
    {
        if (!in_array($request->method(), self::STATEFUL_METHODS, true)) {
            return $next($request);
        }

        $token = (string) ($request->form()['_csrf_token'] ?? $request->header('x-csrf-token', '') ?? '');
        if (!$this->csrf->isValid($token)) {
            return Response::html($this->page(), 419);
        }

        return $next($request);
    }

    private function page(): string
    {
        return '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Session expired</title>'
            . '<style>body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#f5f7fb;margin:0;display:grid;place-items:center;min-height:100vh}'
            . '.card{background:#fff;border:1px solid #d8dee9;border-radius:14px;box-shadow:0 10px 30px rgba(15,23,42,.08);padding:28px;max-width:460px;width:92%;text-align:center}'
            . 'h1{margin:.2em 0;font-size:1.4rem;color:#0f172a}p{color:#475569;line-height:1.5}'
            . 'a{display:inline-block;margin-top:14px;padding:10px 18px;border-radius:8px;background:#0f172a;color:#fff;text-decoration:none;font-weight:600}</style>'
            . '</head><body><main class="card"><h1>Your session security token expired</h1>'
            . '<p>For your protection this action was blocked. Please go back, reload the page and try again.</p>'
            . '<a href="/admin">Back to admin</a></main></body></html>';
    }
}
