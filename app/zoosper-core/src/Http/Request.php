<?php

declare(strict_types=1);

namespace Zoosper\Core\Http;

final readonly class Request
{
    /**
     * @param array<string, string> $headers
     * @param array<string, string> $query
     */
    public function __construct(
        private string $method,
        private string $path,
        private array $headers = [],
        private string $body = '',
        private array $query = [],
        private string $host = 'localhost',
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $queryString = parse_url($uri, PHP_URL_QUERY) ?: '';
        parse_str($queryString, $query);

        return new self(
            method: strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            path: '/' . trim($path, '/'),
            headers: function_exists('getallheaders') ? array_change_key_case(getallheaders(), CASE_LOWER) : [],
            body: file_get_contents('php://input') ?: '',
            query: array_map(static fn (mixed $value): string => (string) $value, $query),
            host: strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'localhost')),
        );
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

    public function query(string $key, ?string $default = null): ?string
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function json(): array
    {
        $decoded = json_decode($this->body, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function form(): array
    {
        return $_POST;
    }
}
