<?php
declare(strict_types=1);

namespace Zoosper\Core\Http;
final readonly class Request
{
    public function __construct(private string $method, private string $path, private array $headers = [], private string $body = '')
    {
    }

    public static function fromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $p = parse_url($uri, PHP_URL_PATH) ?: '/';
        return new self(strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')), '/' . trim($p, '/'), function_exists('getallheaders') ? array_change_key_case(getallheaders(), CASE_LOWER) : [], file_get_contents('php://input') ?: '');
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path === '//' ? '/' : $this->path;
    }

    public function json(): array
    {
        $d = json_decode($this->body, true);
        return is_array($d) ? $d : [];
    }

    public function form(): array
    {
        return $_POST;
    }
}
