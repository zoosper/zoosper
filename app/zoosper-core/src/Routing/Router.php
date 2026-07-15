<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

use Throwable;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Log\ErrorHandler;

/**
 * Minimal HTTP router.
 *
 * Phase 1.27: dispatch() now wraps handler execution in a central
 * catch-and-log safety net. Any uncaught exception from a controller/handler is
 * written to the exception log (redacted by LocalLogger) and a safe 500 is
 * returned, instead of surfacing an unlogged fatal.
 */
final class Router
{
    /**
     * @var array<string, callable(Request): Response>
     */
    private array $routes = [];

    /**
     * @var callable(Request): Response|null
     */
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
        $this->routes[strtoupper($method) . ' ' . $this->normalise($path)] = $handler;
    }

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

    private function normalise(string $path): string
    {
        $normalised = '/' . trim($path, '/');

        return $normalised === '//' ? '/' : $normalised;
    }
}
