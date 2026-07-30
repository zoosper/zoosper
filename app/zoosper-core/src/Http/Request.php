<?php

declare(strict_types=1);

namespace Zoosper\Core\Http;

use Zoosper\Core\Site\SiteContext;

/**
 * Immutable HTTP request value object.
 *
 * Phase 1.34a: the resolved site context is carried as an immutable property on
 * the request itself. Phase 1.35 adds immutable route parameters extracted by
 * the router from parameterised paths such as /admin/pages/{id}.
 *
 * CORRECTNESS FIX (confirmed 2026-07-30, flagged by an external reviewer
 * pass): form() previously read the live $_POST superglobal directly on
 * every call, unlike every other accessor on this class (host, path,
 * headers, query, siteContext, routeParams), which all read from an
 * immutable, constructor-injected property. This broke the entire point of
 * this being an immutable value object:
 * - A manually-constructed Request (tests, sub-requests, future queue
 *   workers) could never actually control its own form data — form()
 *   silently ignored whatever was passed in and read the global instead.
 * - Tests had to mutate global $_POST state around each call instead of
 *   simply constructing the object with the data they intended it to have
 *   (see the fixed CsrfMiddlewareTest for the before/after).
 *
 * form() now reads from a genuinely immutable $form property, captured
 * once in fromGlobals() from the live $_POST at the moment the real
 * top-level request is bootstrapped — exactly the same pattern already
 * used for every other property on this class. No backward-compatibility
 * shim was added (per explicit project decision: 100% AI-authored,
 * pre-launch, no external users yet — this is the correct long-term
 * behaviour, not a stopgap).
 */
final readonly class Request
{
    /**
     * @param array<string, string> $headers
     * @param array<string, string> $query
     * @param array<string, mixed> $form
     * @param array<string, string> $routeParams
     */
    public function __construct(
        private string $method,
        private string $path,
        private array $headers = [],
        private string $body = '',
        private array $query = [],
        private string $host = 'localhost',
        private ?string $clientIp = null,
        private ?SiteContext $siteContext = null,
        private array $routeParams = [],
        private array $form = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $queryString = parse_url($uri, PHP_URL_QUERY) ?: '';
        parse_str($queryString, $query);
        $headers = function_exists('getallheaders') ? array_change_key_case(getallheaders(), CASE_LOWER) : [];

        return new self(
            method: strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            path: '/' . trim($path, '/'),
            headers: $headers,
            body: file_get_contents('php://input') ?: '',
            query: array_map(static fn (mixed $value): string => (string) $value, $query),
            host: strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'localhost')),
            clientIp: (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            // Captured ONCE here, at the true request boundary — the only
            // place this class should ever read a superglobal. Every other
            // construction path (manual, tests, sub-requests) provides
            // $form explicitly through the constructor instead.
            form: $_POST,
        );
    }

    public function withSiteContext(SiteContext $siteContext): self
    {
        return new self(
            method: $this->method,
            path: $this->path,
            headers: $this->headers,
            body: $this->body,
            query: $this->query,
            host: $this->host,
            clientIp: $this->clientIp,
            siteContext: $siteContext,
            routeParams: $this->routeParams,
            form: $this->form,
        );
    }

    /** @param array<string, scalar|null> $routeParams */
    public function withRouteParams(array $routeParams): self
    {
        $normalised = [];
        foreach ($routeParams as $key => $value) {
            if (is_string($key) && $key !== '') {
                $normalised[$key] = (string) $value;
            }
        }

        return new self(
            method: $this->method,
            path: $this->path,
            headers: $this->headers,
            body: $this->body,
            query: $this->query,
            host: $this->host,
            clientIp: $this->clientIp,
            siteContext: $this->siteContext,
            routeParams: $normalised,
            form: $this->form,
        );
    }

    /**
     * Return a new Request carrying the given form data, leaving every
     * other property unchanged. Additive — mirrors withSiteContext()/
     * withRouteParams() so callers that build a base Request and then need
     * to attach form data (e.g. a test, or a sub-request built from an
     * existing one) have an explicit, immutable way to do so instead of
     * touching $_POST.
     *
     * @param array<string, mixed> $form
     */
    public function withForm(array $form): self
    {
        return new self(
            method: $this->method,
            path: $this->path,
            headers: $this->headers,
            body: $this->body,
            query: $this->query,
            host: $this->host,
            clientIp: $this->clientIp,
            siteContext: $this->siteContext,
            routeParams: $this->routeParams,
            form: $form,
        );
    }

    public function siteContext(): ?SiteContext
    {
        return $this->siteContext;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path === '//' ? '/' : $this->path;
    }

    public function host(): string
    {
        return explode(':', $this->host)[0];
    }

    public function clientIp(): ?string
    {
        return $this->clientIp !== '' ? $this->clientIp : null;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $key = strtolower($name);

        return $this->headers[$key] ?? $default;
    }

    public function userAgent(): ?string
    {
        return $this->header('user-agent');
    }

    public function query(string $key, ?string $default = null): ?string
    {
        return $this->query[$key] ?? $default;
    }

    public function routeParam(string $key, ?string $default = null): ?string
    {
        return $this->routeParams[$key] ?? $default;
    }

    /** @return array<string, string> */
    public function routeParams(): array
    {
        return $this->routeParams;
    }

    /** @return array<string, mixed> */
    public function json(): array
    {
        $decoded = json_decode($this->body, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Return this request's form data.
     *
     * FIXED: now reads the immutable $form property captured at
     * construction time (see fromGlobals()/withForm()), instead of reading
     * the live $_POST superglobal directly on every call.
     *
     * @return array<string, mixed>
     */
    public function form(): array
    {
        return $this->form;
    }
}
