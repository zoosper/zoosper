<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

use InvalidArgumentException;
use Throwable;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Log\ErrorHandler;

/**
 * Minimal HTTP router.
 *
 * Exact static routes are looked up first. Parameterised routes are compiled
 * once during registration and evaluated only if no exact route matches.
 */
final class Router
{
    /** @var array<string, callable(Request): Response> */
    private array $routes = [];

    /**
     * @var array<string, list<array{path: string, regex: string, params: list<string>, handler: callable(Request): Response}>>
     */
    private array $parameterRoutes = [];

    /** @var callable(Request): Response|null */
    private $fallback = null;

    public function __construct(private ?ErrorHandler $errorHandler = null)
    {
    }

    public function get(string $path, callable $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    public function map(string $method, string $path, callable $handler): void
    {
        $method = strtoupper($method);
        $path = $this->normalise($path);

        if (!$this->hasPathParameter($path)) {
            $this->routes[$method . ' ' . $path] = $handler;
            return;
        }

        $this->parameterRoutes[$method][] = $this->compileParameterRoute($path, $handler);
    }

    public function fallback(callable $handler): void
    {
        $this->fallback = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $this->normalise($request->path());
        $key = $method . ' ' . $path;

        if (isset($this->routes[$key])) {
            return $this->call($this->routes[$key], $request);
        }

        foreach ($this->parameterRoutes[$method] ?? [] as $route) {
            if (preg_match($route['regex'], $path, $matches) !== 1) {
                continue;
            }

            $params = [];
            foreach ($route['params'] as $name) {
                $params[$name] = rawurldecode((string) ($matches[$name] ?? ''));
            }

            return $this->call($route['handler'], $request->withRouteParams($params));
        }

        if ($this->fallback !== null) {
            return $this->call($this->fallback, $request);
        }

        return Response::html('<h1>404</h1>', 404);
    }

    /**
     * @param callable(Request): Response $handler
     */
    private function call(callable $handler, Request $request): Response
    {
        try {
            return $handler($request);
        } catch (Throwable $exception) {
            $this->errorHandler?->logException($exception, [
                'path' => $request->path(),
                'method' => $request->method(),
            ]);

            if (str_starts_with($request->path(), '/api/')) {
                return Response::json([
                    'success' => false,
                    'error' => [
                        'code' => 'server_error',
                        'message' => 'Internal server error.',
                    ],
                ], 500);
            }

            return Response::html('<h1>500</h1><p>An unexpected error occurred. The details have been logged.</p>', 500);
        }
    }

    /**
     * @param callable(Request): Response $handler
     * @return array{path: string, regex: string, params: list<string>, handler: callable(Request): Response}
     */
    private function compileParameterRoute(string $path, callable $handler): array
    {
        $params = [];
        $segments = trim($path, '/') === '' ? [] : explode('/', trim($path, '/'));
        $regexParts = [];

        foreach ($segments as $segment) {
            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)(?::(.+))?\}$/', $segment, $matches) === 1) {
                $name = $matches[1];
                if (in_array($name, $params, true)) {
                    throw new InvalidArgumentException('Duplicate route parameter name: ' . $name);
                }

                $constraint = $matches[2] ?? '[^/]+';
                $constraint = str_replace('#', '\\#', $constraint);
                $params[] = $name;
                $regexParts[] = '(?P<' . $name . '>' . $constraint . ')';
                continue;
            }

            if (str_contains($segment, '{') || str_contains($segment, '}')) {
                throw new InvalidArgumentException('Invalid route parameter segment in path: ' . $path);
            }

            $regexParts[] = preg_quote($segment, '#');
        }

        $regex = '#^/' . implode('/', $regexParts) . '$#';
        if ($path === '/') {
            $regex = '#^/$#';
        }

        if (@preg_match($regex, '') === false) {
            throw new InvalidArgumentException('Invalid route parameter constraint in path: ' . $path);
        }

        return [
            'path' => $path,
            'regex' => $regex,
            'params' => $params,
            'handler' => $handler,
        ];
    }

    private function hasPathParameter(string $path): bool
    {
        return str_contains($path, '{') || str_contains($path, '}');
    }

    private function normalise(string $path): string
    {
        $normalised = '/' . trim($path, '/');

        return $normalised === '//' ? '/' : $normalised;
    }
}
