<?php
declare(strict_types=1);

namespace Zoosper\Page\Controller;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class HomeController
{
    public function index(Request $r): Response
    {
        return Response::html('<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Zoosper</title></head><body style="font-family:system-ui;margin:3rem"><h1>Hello from Zoosper</h1><p>Phase 0.2: migrations, persistent admin users, login/logout and roles.</p><p>Try /admin/login or /api/v1/health.</p></body></html>');
    }
}
