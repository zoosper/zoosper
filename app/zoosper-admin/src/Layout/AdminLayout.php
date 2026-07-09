<?php

declare(strict_types=1);

namespace Zoosper\Admin\Layout;

use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuItem;
use Zoosper\Auth\Model\AdminUser;

final readonly class AdminLayout
{
    public function __construct(private AdminMenu $menu)
    {
    }

    public function render(string $title, string $content, ?AdminUser $user, string $active = 'dashboard'): string
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $userName = $user !== null ? htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') : 'Guest';
        $navigation = $user !== null ? $this->navigation($user, $active) : '';

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$safeTitle} - Zoosper Admin</title>
    <style>
        :root { --z-primary:#0f766e; --z-dark:#0f172a; --z-page:#f8fafc; --z-card:#fff; --z-border:#d9e2ec; --z-text:#102a43; --z-muted:#64748b; }
        * { box-sizing: border-box; }
        body { margin:0; background:var(--z-page); color:var(--z-text); font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
        .admin-shell { min-height:100vh; display:grid; grid-template-columns:260px minmax(0,1fr); }
        .admin-sidebar { background:var(--z-dark); color:white; padding:1.25rem; }
        .brand { font-size:1.25rem; font-weight:800; margin-bottom:1.5rem; }
        .admin-nav { display:grid; gap:.35rem; }
        .admin-nav a { color:#cbd5e1; text-decoration:none; padding:.65rem .75rem; border-radius:.65rem; display:block; }
        .admin-nav a.active, .admin-nav a:hover { background:rgba(255,255,255,.10); color:white; }
        .admin-main { min-width:0; }
        .admin-topbar { background:var(--z-card); border-bottom:1px solid var(--z-border); padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center; }
        .admin-content { padding:1.5rem; max-width:1180px; }
        .card, .page-form { background:var(--z-card); border:1px solid var(--z-border); border-radius:1rem; padding:1.25rem; box-shadow:0 10px 24px rgba(15,23,42,.04); }
        .cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
        a { color:var(--z-primary); }
        table { width:100%; border-collapse:collapse; background:var(--z-card); border:1px solid var(--z-border); border-radius:1rem; overflow:hidden; }
        th,td { border-bottom:1px solid #e2e8f0; padding:.75rem; text-align:left; vertical-align:top; }
        th { background:#f1f5f9; }
        input,textarea,select { width:100%; padding:.7rem; margin-top:.25rem; border:1px solid #cbd5e1; border-radius:.5rem; font:inherit; }
        label { display:block; margin:1rem 0; }
        button,.button { display:inline-block; padding:.65rem .9rem; border:0; border-radius:.5rem; background:var(--z-primary); color:white; text-decoration:none; cursor:pointer; font:inherit; }
        .secondary { background:#475569; }
        .toolbar,.actions { display:flex; gap:.75rem; align-items:center; flex-wrap:wrap; margin:1rem 0; }
        .inline-form { display:inline; }
        .inline-form button { padding:.35rem .65rem; background:#334155; }
        .error { padding:.75rem; border:1px solid #fecaca; background:#fef2f2; color:#991b1b; border-radius:.5rem; }
        .checkbox { display:flex; gap:.5rem; align-items:center; }
        .checkbox input { width:auto; }
        .muted { color:var(--z-muted); }
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar"><div class="brand">Zoosper</div>{$navigation}</aside>
    <section class="admin-main">
        <header class="admin-topbar"><strong>{$safeTitle}</strong><span class="muted">{$userName}</span></header>
        <main class="admin-content">{$content}</main>
    </section>
</div>
</body>
</html>
HTML;
    }

    private function navigation(AdminUser $user, string $active): string
    {
        $links = array_map(fn (AdminMenuItem $item): string => $this->navigationLink($item, $active), $this->menu->itemsFor($user));
        return '<nav class="admin-nav">' . implode('', $links) . '</nav>';
    }

    private function navigationLink(AdminMenuItem $item, string $active): string
    {
        $url = htmlspecialchars($item->url, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($item->label, ENT_QUOTES, 'UTF-8');
        $class = $item->code === $active ? ' class="active"' : '';
        return '<a href="' . $url . '"' . $class . '>' . $label . '</a>';
    }
}
