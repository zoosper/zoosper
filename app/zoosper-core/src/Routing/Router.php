<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

use Closure;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final class Router
{
    /** @var array<string, callable(Request): Response> */
    private array $routes = [];

    /** @var callable(Request): Response|null */
    private $fallback = null;

    /** @param callable(Request): Response $handler */
    public function get(string $path, callable $handler): void
    {
        $this->routes['GET ' . $this->normalise($path)] = $handler;
    }

    /** @param callable(Request): Response $handler */
    public function fallback(callable $handler): void
    {
        $this->fallback = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $key = $request->method() . ' ' . $this->normalise($request->path());
        $handler = $this->routes[$key] ?? $this->fallback;

        if ($handler === null) {
            return Response::html('<h1>404</h1>', 404);
        }

        return $handler($request);
    }

    private function normalise(string $path): string
    {
        $normalised = '/' . trim($path, '/');
        return $normalised === '//' ? '/' : $normalised;
    }
}
