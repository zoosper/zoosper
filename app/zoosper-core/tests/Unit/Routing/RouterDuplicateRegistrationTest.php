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
