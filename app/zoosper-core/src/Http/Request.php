<?php

declare(strict_types=1);

namespace Zoosper\Core\Http;

use Zoosper\Core\Site\SiteContext;

/** Immutable HTTP request value object. */
final readonly class Request
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $query
     * @param array<string, string> $routeParams
     * @param array<string, mixed> $form
     * @param array<string, mixed> $files Uploaded-file entries captured at the HTTP boundary.
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
        private array $files = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $queryString = parse_url($uri, PHP_URL_QUERY) ?: '';
        parse_str($queryString, $query);
        $headers = function_exists('getallheaders')
            ? array_change_key_case(getallheaders(), CASE_LOWER)
            : [];

        return new self(
            method: strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            path: '/' . trim($path, '/'),
            headers: $headers,
            body: file_get_contents('php://input') ?: '',
            query: self::normaliseInputMap($query),
            host: strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'localhost')),
            clientIp: TrustedProxyResolver::fromEnvironment()->clientIp($_SERVER),
            form: $_POST,
            files: $_FILES,
        );
    }

    public function withSiteContext(SiteContext $siteContext): self
    {
        return new self($this->method, $this->path, $this->headers, $this->body, $this->query,
            $this->host, $this->clientIp, $siteContext, $this->routeParams, $this->form, $this->files);
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
        return new self($this->method, $this->path, $this->headers, $this->body, $this->query,
            $this->host, $this->clientIp, $this->siteContext, $normalised, $this->form, $this->files);
    }

    /** @param array<string, mixed> $form */
    public function withForm(array $form): self
    {
        return new self($this->method, $this->path, $this->headers, $this->body, $this->query,
            $this->host, $this->clientIp, $this->siteContext, $this->routeParams, $form, $this->files);
    }

    public function siteContext(): ?SiteContext { return $this->siteContext; }
    public function method(): string { return $this->method; }
    public function path(): string { return $this->path === '//' ? '/' : $this->path; }
    public function host(): string { return explode(':', $this->host)[0]; }
    public function clientIp(): ?string { return $this->clientIp !== '' ? $this->clientIp : null; }

    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    public function userAgent(): ?string { return $this->header('user-agent'); }

    /** Scalar query accessor. Array-valued parameters return the default. */
    public function query(string $key, ?string $default = null): ?string
    {
        $value = $this->query[$key] ?? null;
        return is_scalar($value) ? (string) $value : $default;
    }

    /** @return list<string> */
    public function queryList(string $key): array
    {
        return self::stringList($this->query[$key] ?? []);
    }

    /** @return array<string, mixed> */
    public function queryParams(): array
    {
        return $this->query;
    }

    public function routeParam(string $key, ?string $default = null): ?string
    {
        return $this->routeParams[$key] ?? $default;
    }

    /** @return array<string, string> */
    public function routeParams(): array { return $this->routeParams; }

    /** @return array<string, mixed> */
    public function json(): array
    {
        $decoded = json_decode($this->body, true);
        return is_array($decoded) ? $decoded : [];
    }

    /** @return array<string, mixed> */
    public function form(): array { return $this->form; }

    /**
     * Returns one uploaded-file entry captured by fromGlobals().
     *
     * @return array<string, mixed>
     */
    public function uploadedFile(string $key): array
    {
        $file = $this->files[$key] ?? null;

        return is_array($file) ? $file : [];
    }

    /** @param array<string, mixed> $values @return array<string, mixed> */
    private static function normaliseInputMap(array $values): array
    {
        $normalised = [];
        foreach ($values as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            $normalised[$key] = is_array($value)
                ? self::stringList($value)
                : (is_scalar($value) ? (string) $value : '');
        }
        return $normalised;
    }

    /** @return list<string> */
    private static function stringList(mixed $value): array
    {
        $result = [];
        $append = static function (mixed $item) use (&$result): void {
            if (!is_scalar($item)) {
                return;
            }
            $item = trim((string) $item);
            if ($item !== '' && !in_array($item, $result, true)) {
                $result[] = $item;
            }
        };
        is_array($value) ? array_walk_recursive($value, $append) : $append($value);
        return $result;
    }
}
