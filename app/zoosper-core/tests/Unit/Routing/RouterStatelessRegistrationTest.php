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












it('keeps unmatched read-only fallback requests stateless without weakening explicit stateful routes', function (): void {
    $router = new Router();
    $router->map('GET', '/admin/login', static fn (): Response => Response::html('login'), false);
    $router->map('POST', '/admin/login', static fn (): Response => Response::html('login'), false);

    expect($router->isStateless(new Request('GET', '/published-page')))->toBeTrue()
        ->and($router->isStateless(new Request('HEAD', '/published-page')))->toBeTrue()
        ->and($router->isStateless(new Request('GET', '/missing-page')))->toBeTrue()
        ->and($router->isStateless(new Request('GET', '/admin/login')))->toBeFalse()
        ->and($router->isStateless(new Request('POST', '/admin/login')))->toBeFalse()
        ->and($router->isStateless(new Request('POST', '/published-page')))->toBeFalse();
});
