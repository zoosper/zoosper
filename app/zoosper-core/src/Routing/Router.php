<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

use InvalidArgumentException;
use Throwable;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Http\CorsPolicy;
use Zoosper\Core\Error\ErrorHandler;

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
    /** @var array<string,bool> */
    private array $statelessRoutes = [];
    private CorsPolicy $cors;

    /** @var callable(Request): Response|null */
    private $fallback = null;

    public function __construct(private ?ErrorHandler $errorHandler = null, ?CorsPolicy $cors = null)
    {
        $this->cors = $cors ?? CorsPolicy::fromEnvironment();
    }

    public function get(string $path, callable $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    public function map(string $method, string $path, callable $handler, bool $stateless = false): void
    {
        $method = strtoupper($method);
        $path = $this->normalise($path);
        $this->statelessRoutes[$method . ' ' . $path] = $stateless;

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
        $method = strtoupper($request->method());
        $path = $this->normalise($request->path());
        $origin = $request->header('origin');
        $allowed = $this->allowedMethods($path);
        if ($method === 'OPTIONS' && str_starts_with($path, '/api/') && $allowed !== []) {
            $headers = ['Allow' => implode(', ', $allowed)];
            return Response::raw('', 204, array_merge($headers, $this->cors->headersFor((string) $origin, true)));
        }
        $dispatchMethod = $method === 'HEAD' && in_array('GET', $allowed, true) ? 'GET' : $method;
        $match = $this->match($dispatchMethod, $path, $request);
        if ($match !== null) {
            $response = $this->call($match[0], $match[1]);
            if (str_starts_with($path, '/api/')) $response = $response->withHeaders($this->cors->headersFor((string) $origin));
            return $method === 'HEAD' ? $response->withoutBody() : $response;
        }
        if ($allowed !== []) {
            return Response::raw('', 405, ['Allow' => implode(', ', $allowed)]);
        }
        if ($this->fallback !== null) return $this->call($this->fallback, $request);
        return Response::html('<h1>404</h1>', 404);
    }
    public function isStateless(Request $request): bool
    {
        $method = strtoupper($request->method());
        $path = $this->normalise($request->path());
        if ($method === 'OPTIONS' && str_starts_with($path, '/api/') && $this->allowedMethods($path) !== []) return true;
        if ($method === 'HEAD' && in_array('GET', $this->allowedMethods($path), true)) $method = 'GET';
        if (array_key_exists($method . ' ' . $path, $this->statelessRoutes)) return $this->statelessRoutes[$method . ' ' . $path];
        foreach ($this->parameterRoutes[$method] ?? [] as $route) if (preg_match($route['regex'], $path) === 1) return $this->statelessRoutes[$method . ' ' . $route['path']] ?? false;
        return false;
    }
    /** @return list<string> */
    public function allowedMethods(string $path): array
    {
        $path=$this->normalise($path);$methods=[];
        foreach(array_keys($this->routes) as $key){[$method,$registered]=explode(' ',$key,2);if($registered===$path)$methods[]=$method;}
        foreach($this->parameterRoutes as $method=>$routes)foreach($routes as $route)if(preg_match($route['regex'],$path)===1)$methods[]=$method;
        if(in_array('GET',$methods,true))$methods[]='HEAD';
        $methods=array_values(array_unique($methods));sort($methods);return $methods;
    }
    /** @return array{0:callable,1:Request}|null */
    private function match(string $method,string $path,Request $request):?array
    {
        $key=$method.' '.$path;if(isset($this->routes[$key]))return [$this->routes[$key],$request];
        foreach($this->parameterRoutes[$method]??[] as $route){if(preg_match($route['regex'],$path,$matches)!==1)continue;$params=[];foreach($route['params'] as $name)$params[$name]=rawurldecode((string)($matches[$name]??''));return [$route['handler'],$request->withRouteParams($params)];}
        return null;
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

            return $this->errorHandler?->httpResponse(
                $exception,
                str_starts_with($request->path(), '/api/'),
            ) ?? Response::html('<h1>500</h1><p>An unexpected error occurred. The details have been logged.</p>', 500);
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
