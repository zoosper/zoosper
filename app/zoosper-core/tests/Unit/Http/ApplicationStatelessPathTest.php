<?php

declare(strict_types=1);
use Zoosper\Core\Http\Application;
it('keeps public machine-readable SEO paths stateless',function(){expect(Application::isStatelessPublicPath('/sitemap.xml'))->toBeTrue()->and(Application::isStatelessPublicPath('/robots.txt'))->toBeTrue()->and(Application::isStatelessPublicPath('/admin'))->toBeFalse()->and(Application::isStatelessPublicPath('/'))->toBeFalse();});










