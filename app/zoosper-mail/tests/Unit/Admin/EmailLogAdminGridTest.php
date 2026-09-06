<?php

declare(strict_types=1);

use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Grid\GridCriteria;
use Zoosper\Mail\Admin\EmailLogGrid;
use Zoosper\Pagination\Pager;

test('Email Logs uses a stable paginated Admin Grid with safe filters and no body columns', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE smtp_email_log (id INTEGER PRIMARY KEY AUTOINCREMENT,status TEXT,from_email TEXT,to_emails TEXT,subject TEXT,text_body TEXT,html_body TEXT,created_at TEXT)');
    for ($i = 1; $i <= 25; $i++) {
        $pdo->exec("INSERT INTO smtp_email_log(status,from_email,to_emails,subject,created_at) VALUES ('sent','from@example.test','to@example.test','Message {$i}','2026-09-06 00:00:00')");
    }
    $grid = new EmailLogGrid($pdo);
    $result = $grid->paginate(new GridCriteria(new Pager(2, 20), 'created_at', 'desc', ['email' => 'to@example.test']));
    expect(EmailLogGrid::KEY)->toBe('admin.email-logs')
        ->and($result->total)->toBe(25)
        ->and($result->items)->toHaveCount(5)
        ->and($grid->definition()->allColumnKeys())->not->toContain('text_body', 'html_body');
});

test('Email Log detail bounds escaped message source and Mail owns its asset', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = file_get_contents($root . '/app/zoosper-mail/src/Controller/EmailLogAdminController.php');
    $css = file_get_contents($root . '/app/zoosper-mail/resources/admin/css/email-log.css');
    expect($controller)->toContain('class="mail-log-body"')
        ->toContain("html_body")
        ->toContain('$this->e(')
        ->and($css)->toContain('white-space: pre-wrap')->toContain('overflow-wrap: anywhere')->toContain('max-width: 100%');
});

test('Email Logs composes native pagination nodes into one feature-owned footer', function (): void {
    $root = dirname(__DIR__, 5);
    $css = file_get_contents($root . '/app/zoosper-mail/resources/admin/css/email-log.css');
    $script = file_get_contents($root . '/app/zoosper-mail/resources/admin/js/email-log-workspace.js');
    expect($css)->toContain('[data-grid-export] { display: none; }')
        ->toContain('.email-logs-index__pagination {')
        ->toContain('grid-template-columns: minmax(7rem, 1fr) auto minmax(7rem, 1fr);')
        ->toContain('.email-logs-index__previous { grid-column: 1;')
        ->toContain('.email-logs-index__next { grid-column: 3;')
        ->and($script)->toContain("footer.append(previousControl, controls, nextControl)")
        ->toContain("legacy.remove()")
        ->toContain("data-email-logs-page");
});
