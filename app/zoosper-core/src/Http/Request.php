<?php

declare(strict_types=1);

namespace Zoosper\Core\Http;

final readonly class Request
{
    /** @param array<string, string> $headers */
    public function __construct(
        private string $method,
        private string $path,
        private array $headers = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        return new self(
            method: strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            path: '/' . trim($path, '/'),
            headers: function_exists('getallheaders') ? array_change_key_case(getallheaders(), CASE_LOWER) : [],
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

    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }
}
