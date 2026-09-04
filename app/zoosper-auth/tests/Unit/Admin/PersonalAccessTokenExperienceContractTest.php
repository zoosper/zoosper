<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\PersonalAccessTokenScopePresenter;
use Zoosper\Auth\Token\PersonalAccessTokenService;

it('presents every canonical scope once in stable domain groups', function (): void {
    $groups = (new PersonalAccessTokenScopePresenter())->groups(PersonalAccessTokenService::SCOPES);
    $presented = [];
    foreach ($groups as $group) {
        foreach ($group['scopes'] as $scope) {
            $presented[] = $scope['code'];
        }
    }

    expect(array_column($groups, 'label'))->toBe(['Pages', 'Media', 'Menus', 'Roles & Permissions', 'URL rewrites', 'Sites', 'Themes'])
        ->and($presented)->toBe(PersonalAccessTokenService::SCOPES)
        ->and(array_filter(
            array_merge(...array_column($groups, 'scopes')),
            static fn (array $scope): bool => $scope['kind'] === 'destructive',
        ))->toHaveCount(2);
});

it('ships a CSP-safe responsive Auth-owned PAT experience', function (): void {
    $root = dirname(__DIR__, 3);
    $project = dirname($root, 2);
    $template = (string) file_get_contents($root . '/resources/views/admin/access-tokens/index.latte');
    $sourceCss = (string) file_get_contents($root . '/resources/assets/admin/css/personal-access-tokens.css');
    $sourceJs = (string) file_get_contents($root . '/resources/assets/admin/js/personal-access-tokens.js');
    $publicCss = (string) file_get_contents($project . '/public/assets/admin/css/personal-access-tokens.css');
    $publicJs = (string) file_get_contents($project . '/public/assets/admin/js/personal-access-tokens.js');
    $assets = require $root . '/config/admin_assets.php';

    expect($assets)->toHaveKeys(['zoosper-personal-access-tokens-style', 'zoosper-personal-access-tokens-runtime'])
        ->and($assets['zoosper-personal-access-tokens-style']['path'])->toBe('/assets/admin/css/personal-access-tokens.css?v=621422fcf72a')
        ->and($assets['zoosper-personal-access-tokens-runtime']['path'])->toBe('/assets/admin/js/personal-access-tokens.js?v=79ede8c2c657')
        ->and($assets['zoosper-personal-access-tokens-style']['sort_order'])->toBe(88)
        ->and($assets['zoosper-personal-access-tokens-runtime']['sort_order'])->toBe(88)
        ->and($sourceCss)->toBe($publicCss)
        ->and($sourceJs)->toBe($publicJs)
        ->and($template)->toContain('data-pat-screen')
        ->toContain('data-pat-scope-group')
        ->toContain('data-pat-copy')
        ->toContain('name="_csrf_token"')
        ->toContain('method="post"')
        ->toContain('{$gridHtml|noescape}')
        ->not->toMatch('/<(?:script|style)\b/i')
        ->not->toMatch('/\son[a-z]+\s*=/i')
        ->not->toContain('style=')
        ->and($sourceJs)->not->toContain('innerHTML')
        ->not->toContain('insertAdjacentHTML')
        ->not->toContain('fetch(')
        ->not->toContain('.submit(')
        ->and($sourceCss)->toContain(':root[data-admin-theme="dark"]')
        ->toContain('@media (max-width: 760px)')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

it('retains owner-scoped POST-only PAT security boundaries', function (): void {
    $root = dirname(__DIR__, 3);
    $routes = require $root . '/config/admin_routes.php';
    $controller = (string) file_get_contents($root . '/src/Admin/Controller/PersonalAccessTokenAdminController.php');
    $repository = (string) file_get_contents($root . '/src/Token/PersonalAccessTokenRepository.php');
    $map = [];
    foreach ($routes as $route) {
        $map[$route['method'] . ' ' . $route['path']] = true;
    }

    expect($map)->toHaveKeys([
        'GET /admin/access-tokens',
        'POST /admin/access-tokens/create',
        'POST /admin/access-tokens/{id:\d+}/revoke',
    ])->and($controller)->toContain('allForUser($user->id)')
        ->toContain('revoke($id, $user->id')
        ->toContain("'personal_access_token.issued'")
        ->toContain("'personal_access_token.revoked'")
        ->not->toContain("'token_hash'")
        ->and($repository)->toContain('admin_user_id=:owner')
        ->toContain('admin_user_id=:id');
});










