<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Routing\FallbackHandlerInterface;
use Zoosper\Core\Site\SiteContext;
use Zoosper\UrlRewrite\Repository\UrlRewriteRepository;
use Zoosper\UrlRewrite\Routing\UrlRewriteFallbackHandler;
use Zoosper\UrlRewrite\Service\UrlRewriteResolver;

function rewriteSecurityHandler(string $target, int $type = 301): UrlRewriteFallbackHandler
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE url_rewrites(id INTEGER PRIMARY KEY AUTOINCREMENT, site_id INTEGER, request_path TEXT, target_path TEXT, entity_type TEXT, entity_id INTEGER, redirect_type INTEGER, is_active INTEGER, created_at TEXT, updated_at TEXT)');
    $repository = new UrlRewriteRepository($pdo);
    $repository->save(null, 1, 'old', $target, $type);
    $next = new class implements FallbackHandlerInterface {
        public function supports(object $request): bool { return true; }
        public function handle(object $request): mixed { return Response::html('fallback', 404); }
    };
    return new UrlRewriteFallbackHandler(new UrlRewriteResolver($repository), $next);
}

function rewriteSecurityRequest(): Request
{
    return new Request('GET', '/old', siteContext: new SiteContext('', '', '', '', '', '', '', '', '', siteId: 1));
}

it('falls through for unsafe stored redirects', function (string $target, int $type): void {
    $response = rewriteSecurityHandler($target, $type)->handle(rewriteSecurityRequest());
    expect($response->statusCode())->toBe(404)->and($response->headers())->not->toHaveKey('Location');
})->with([
    ['javascript:alert(1)', 301],
    ['/\\evil.example/path', 301],
    ['//evil.example/path', 301],
    ["/safe\r\nX-Injected: yes", 301],
    ['/safe', 200],
    ['/old', 301],
]);

it('continues to serve valid stored redirects', function (string $target, int $type): void {
    $response = rewriteSecurityHandler($target, $type)->handle(rewriteSecurityRequest());
    expect($response->statusCode())->toBe($type)->and($response->headers()['Location'])->toBe($target);
})->with([['/new', 301], ['https://example.test/landing?x=1', 302]]);

it('rejects protocol-relative and control-character targets on save', function (string $target): void {
    expect(fn () => (new \Zoosper\UrlRewrite\Service\RedirectPolicy())->target($target))
        ->toThrow(InvalidArgumentException::class);
})->with(['//evil.example/path', "/safe\r\nX-Injected: yes"]);