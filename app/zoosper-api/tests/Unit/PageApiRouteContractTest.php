<?php

declare(strict_types=1);

it('declares stateless Page list and detail reads', function (): void {
    $routes = require dirname(__DIR__, 2) . '/config/api_routes.php';
    $map=[]; foreach($routes as $route){$map[$route['method'].' '.$route['path']]=$route;}
    expect($map)->toHaveKeys(['GET /api/v1/pages','GET /api/v1/pages/{id:\d+}'])
        ->and($map['GET /api/v1/pages']['stateless'])->toBeTrue()
        ->and($map['GET /api/v1/pages/{id:\d+}']['stateless'])->toBeTrue();
});
