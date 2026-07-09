<?php

declare(strict_types=1);

namespace Zoosper\Page\Controller;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class HomeController
{
    public function index(Request $request): Response
    {
        $html = '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Zoosper</title>'
            . '<style>body{font-family:system-ui;margin:3rem;line-height:1.5;color:#102a43} .hero{max-width:820px;padding:2rem;border-radius:20px;background:linear-gradient(135deg,#e6fffa,#f0f9ff);border:1px solid #bae6fd} code{background:#e2e8f0;padding:.15rem .35rem;border-radius:.35rem}</style>'
            . '</head><body><main class="hero">'
            . '<h1>Hello from Zoosper</h1>'
            . '<p>Modern + Fast + Easy + Secure + API-first CMS.</p>'
            . '<p>Try <code>/api/v1/health</code>, <code>/api/v1/hello</code> or <code>/admin</code>.</p>'
            . '</main></body></html>';

        return Response::html($html);
    }
}
