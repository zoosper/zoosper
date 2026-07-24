<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Wraps the Page Momentum dashboard in a small standalone visual shell.
 */
final class PageMomentumAdminDashboardShell
{
    public function wrap(string $content): string
    {
        return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Momentum · Zoosper Admin</title>
    <style>
        :root {
            color-scheme: light;
            --zsp-bg: #f5f7fb;
            --zsp-surface: #ffffff;
            --zsp-border: #d9e2ef;
            --zsp-text: #182230;
            --zsp-muted: #667085;
            --zsp-accent: #2563eb;
            --zsp-accent-soft: #e8f0ff;
            --zsp-radius: 14px;
            --zsp-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            --zsp-green-bg: #e9f8ef;
            --zsp-green-text: #027a48;
            --zsp-blue-bg: #e8f0ff;
            --zsp-blue-text: #1d4ed8;
            --zsp-purple-bg: #f1e9ff;
            --zsp-purple-text: #6d28d9;
            --zsp-amber-bg: #fff7e6;
            --zsp-amber-text: #b45309;
            --zsp-slate-bg: #eef2f7;
            --zsp-slate-text: #475467;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--zsp-bg);
            color: var(--zsp-text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
        }
        .zoosper-admin-shell { max-width: 1180px; margin: 0 auto; padding: 32px 24px 48px; }
        .zoosper-admin-shell__eyebrow { color: var(--zsp-accent); font-size: 13px; font-weight: 700; letter-spacing: .08em; margin: 0 0 8px; text-transform: uppercase; }
        .zoosper-admin-card { background: var(--zsp-surface); border: 1px solid var(--zsp-border); border-radius: var(--zsp-radius); box-shadow: var(--zsp-shadow); padding: 24px; }
        .zoosper-admin-card--nested { box-shadow: none; padding: 18px; position: relative; overflow: hidden; }
        .zoosper-admin-card--nested::before { background: var(--zsp-accent); content: ""; height: 3px; left: 0; position: absolute; right: 0; top: 0; }
        .zoosper-admin-card--nested h3 { margin: 0 0 8px; font-size: 17px; }
        .zoosper-admin-card--nested p { margin: 0 0 8px; color: var(--zsp-muted); }
        .zsp-status { border-radius: 999px; display: inline-block; font-size: 12px; font-weight: 700; padding: 3px 9px; text-transform: uppercase; }
        .zsp-status--ready, .zsp-status--active { background: var(--zsp-green-bg); color: var(--zsp-green-text); }
        .zsp-status--track { background: var(--zsp-blue-bg); color: var(--zsp-blue-text); }
        .zsp-status--planned { background: var(--zsp-purple-bg); color: var(--zsp-purple-text); }
        .zsp-status--documented { background: var(--zsp-slate-bg); color: var(--zsp-slate-text); }
        .zsp-status--in-progress { background: var(--zsp-amber-bg); color: var(--zsp-amber-text); }
        .zsp-status--neutral { background: var(--zsp-slate-bg); color: var(--zsp-slate-text); }
        .zoosper-admin-card__header h2 { font-size: 34px; line-height: 1.15; margin: 0 0 8px; }
        .zoosper-admin-card__header p, .zoosper-admin-card__footer p { color: var(--zsp-muted); margin: 0; }
        .zoosper-admin-card__footer { border-top: 1px solid var(--zsp-border); margin-top: 26px; padding-top: 18px; }
        .zoosper-admin-card section { margin-top: 26px; }
        .zoosper-admin-card section > h3 { font-size: 21px; margin: 0 0 14px; }
        .zoosper-admin-grid { display: grid; gap: 16px; }
        .zoosper-admin-grid--two { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        code { background: var(--zsp-accent-soft); border-radius: 6px; color: #1d4ed8; padding: 2px 6px; }
    </style>
</head>
<body>
    <main class="zoosper-admin-shell">
        <p class="zoosper-admin-shell__eyebrow">Zoosper Admin</p>
        {$content}
    </main>
</body>
</html>
HTML;
    }
}
