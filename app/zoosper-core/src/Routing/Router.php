<?php
declare(strict_types=1);

namespace Zoosper\Core\Routing;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final class Router
{
    private array $routes = [];
    private $fallback = null;

    public function get(string $p, callable $h): void
    {
        $this->map('GET', $p, $h);
    }

    public function map(string $m, string $p, callable $h): void
    {
        $this->routes[strtoupper($m) . ' ' . $this->norm($p)] = $h;
    }

    private function norm(string $p): string
    {
        $n = '/' . trim($p, '/');
        return $n === '//' ? '/' : $n;
    }

    public function post(string $p, callable $h): void
    {
        $this->map('POST', $p, $h);
    }

    public function fallback(callable $h): void
    {
        $this->fallback = $h;
    }

    public function dispatch(Request $r): Response
    {
        $h = $this->routes[$r->method() . ' ' . $this->norm($r->path())] ?? $this->fallback;
        return $h ? $h($r) : Response::html('<h1>404</h1>', 404);
    }
}
