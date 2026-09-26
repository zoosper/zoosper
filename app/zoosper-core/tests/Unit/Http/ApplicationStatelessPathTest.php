<?php

declare(strict_types=1);
use Zoosper\Core\Http\Application;
it('keeps public machine-readable SEO paths stateless',function(){expect(Application::isStatelessPublicPath('/sitemap.xml'))->toBeTrue()->and(Application::isStatelessPublicPath('/robots.txt'))->toBeTrue()->and(Application::isStatelessPublicPath('/admin'))->toBeFalse()->and(Application::isStatelessPublicPath('/'))->toBeFalse();});












it('delegates unmatched frontend statelessness to router metadata before session start', function (): void {
    $root = dirname(__DIR__, 5);
    $application = (string) file_get_contents($root . '/app/zoosper-core/src/Http/Application.php');
    $router = (string) file_get_contents($root . '/app/zoosper-core/src/Routing/Router.php');

    expect($application)->toContain('!$this->router->isStateless($request)')
        ->and($application)->toContain('session_start();')
        ->and(strpos($application, '!$this->router->isStateless($request)'))
        ->toBeLessThan(strpos($application, 'session_start();'))
        ->and($router)->toContain("return in_array(\$method, ['GET', 'HEAD'], true)")
        ->toContain('$this->allowedMethods($path) === []');
});
