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
 */
final readonly class Request
{
    /**
     * @param array<string, string> $headers
     * @param array<string, string> $query
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
        );
    }

    /**
     * Return a new request instance carrying the resolved site context.
     */
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
        );
    }

    /**
     * Return a new request instance carrying router-extracted path parameters.
     *
     * @param array<string, scalar|null> $routeParams
     */
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

    public function userAgent(): ?string
    {
        return $this->headers['user-agent'] ?? null;
    }

    public function query(string $key, ?string $default = null): ?string
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Return one router-extracted path parameter.
     */
    public function routeParam(string $key, ?string $default = null): ?string
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Return all router-extracted path parameters.
     *
     * @return array<string, string>
     */
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

    /** @return array<string, mixed> */
    public function form(): array
    {
        return $_POST;
    }
}
