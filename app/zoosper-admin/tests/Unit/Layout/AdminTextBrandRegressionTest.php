<?php

declare(strict_types=1);

it('presents the existing Admin brand as compact text only', function (): void {
    $root = dirname(__DIR__, 5);
    $css = (string) file_get_contents($root . '/public/assets/admin/css/admin.css');

    expect($css)
        ->toContain('/* Admin text-brand override: start */')
        ->toContain('.admin-sidebar .brand{display:inline-flex;align-items:center;gap:0}')
        ->toContain('.admin-sidebar .brand img{display:none!important}')
        ->toContain('.admin-sidebar .brand span{display:inline-block}')
        ->toContain('/* Admin text-brand override: end */');
});










