<?php

declare(strict_types=1);

use Zoosper\Core\Http\Response;
use Zoosper\Core\Routing\Router;

it('rejects duplicate static method and path registrations', function (): void {
    $router = new Router();
    $router->get('/admin/example', static fn (): Response => Response::html('first'));

    expect(fn () => $router->get('/admin/example', static fn (): Response => Response::html('second')))
        ->toThrow(InvalidArgumentException::class, 'Duplicate route registration: GET /admin/example');
});

it('rejects duplicate parameterised method and path registrations', function (): void {
    $router = new Router();
    $router->get('/admin/example/{id:\d+}', static fn (): Response => Response::html('first'));

    expect(fn () => $router->get('/admin/example/{id:\d+}', static fn (): Response => Response::html('second')))
        ->toThrow(InvalidArgumentException::class, 'Duplicate route registration: GET /admin/example/{id:\d+}');
});

it('allows the same path for different methods', function (): void {
    $router = new Router();
    $router->get('/admin/example', static fn (): Response => Response::html('get'));
    $router->post('/admin/example', static fn (): Response => Response::html('post'));

    expect($router->allowedMethods('/admin/example'))->toBe(['GET', 'HEAD', 'POST']);
});


it('rejects overlapping parameter route shapes regardless of parameter names or constraints', function (): void {
    $router = new Router();
    $router->get('/api/items/{id:\d+}', static fn (): Response => Response::html('first'));

    expect(fn () => $router->get('/api/items/{slug:[a-z]+}', static fn (): Response => Response::html('second')))
        ->toThrow(InvalidArgumentException::class, 'Overlapping parameter route shape');
});

it('allows distinct parameter route shapes for the same method', function (): void {
    $router = new Router();
    $router->get('/api/items/{id:\d+}', static fn (): Response => Response::html('item'));
    $router->get('/api/categories/{slug}', static fn (): Response => Response::html('category'));

    expect($router->allowedMethods('/api/items/12'))->toContain('GET')
        ->and($router->allowedMethods('/api/categories/books'))->toContain('GET');
});
