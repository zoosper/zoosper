<?php

declare(strict_types=1);

it('keeps the refined PAT experience responsive, themed and CSP safe', function (): void {
    $root = dirname(__DIR__, 3);
    $css = file_get_contents($root . '/resources/assets/admin/css/personal-access-tokens.css');
    $publicCss = file_get_contents(dirname($root, 2) . '/public/assets/admin/css/personal-access-tokens.css');
    $view = file_get_contents($root . '/resources/views/admin/access-tokens/index.latte');

    expect($css)
        ->toBe($publicCss)
        ->toContain('grid-template-columns: repeat(4, minmax(0, 1fr))')
        ->toContain('grid-template-columns: repeat(3, minmax(0, 1fr))')
        ->toContain('grid-template-columns: repeat(2, minmax(0, 1fr))')
        ->toContain('grid-template-columns: 1fr')
        ->toContain(':root[data-admin-theme="dark"]')
        ->toContain('.pat-token-status--active')
        ->toContain('.pat-token-revoke')
        ->toContain('@media (max-width: 47.99rem)')
        ->toContain('.pat-grid-scroll')
        ->toContain('overflow-x: auto')
        ->toContain('overscroll-behavior-inline: contain')
        ->toContain('min-width: 62rem')
        ->not->toMatch('/(?:html|body|admin-shell)[^{]*\{[^}]*overflow-x:\s*hidden/is')
        ->and($view)
        ->toContain('<fieldset class="pat-scope-group"')
        ->toContain('<div class="pat-grid-scroll" tabindex="0" role="region" aria-label="Personal Access Token table and controls">')
        ->toContain('<legend>')
        ->not->toMatch('/\sstyle=/i')
        ->not->toMatch('/\son[a-z]+=/i');
});

it('renders escaped compact PAT cells without weakening owner scoped mutations', function (): void {
    $source = file_get_contents(dirname(__DIR__, 3) . '/src/Admin/Grid/AccessToken/AccessTokenGrid.php');

    expect($source)
        ->toContain("KEY='admin.access-tokens'")
        ->toContain("'admin_user_id=:owner'")
        ->toContain("'owner' => \$this->ownerId")
        ->toContain('private function renderName')
        ->toContain('private function renderScopes')
        ->toContain('private function renderDate')
        ->toContain('private function renderStatus')
        ->toContain('private function renderActions')
        ->toContain('htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE')
        ->toContain('<form method="post"')
        ->toContain('name="_csrf_token"')
        ->toContain('class="button button--danger pat-token-revoke"')
        ->not->toContain('token_hash')
        ->not->toContain('innerHTML');
});
