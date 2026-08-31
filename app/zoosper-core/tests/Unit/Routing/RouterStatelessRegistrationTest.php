<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Routing\Router;

it('retains stateless metadata for static parameterised and implicit HEAD routes', function (): void {
    $router = new Router();
    $router->map('GET', '/api/v1/health', static fn (): Response => Response::json(['ok' => true]), true);
    $router->map('GET', '/api/v1/items/{id:\d+}', static fn (): Response => Response::json(['ok' => true]), true);
    $router->map('POST', '/api/v1/auth/login', static fn (): Response => Response::json(['ok' => true]), false);

    expect($router->isStateless(new Request('GET', '/api/v1/health')))->toBeTrue()
        ->and($router->isStateless(new Request('HEAD', '/api/v1/health')))->toBeTrue()
        ->and($router->isStateless(new Request('GET', '/api/v1/items/12')))->toBeTrue()
        ->and($router->isStateless(new Request('POST', '/api/v1/auth/login')))->toBeFalse();
});










