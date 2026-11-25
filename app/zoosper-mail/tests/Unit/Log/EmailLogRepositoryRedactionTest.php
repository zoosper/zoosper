<?php

declare(strict_types=1);

use Zoosper\Mail\Log\EmailLogRepository;
use Zoosper\Mail\Message\EmailAddress;
use Zoosper\Mail\Message\EmailMessage;

it('redacts logged content while preserving nullable bodies and truncating subjects', function (): void {
    $pdo = new Pdo\Sqlite('sqlite::memory:');
    $pdo->createFunction('NOW', static fn (): string => '2026-01-01 00:00:00');
    $pdo->exec('CREATE TABLE smtp_email_log (id INTEGER PRIMARY KEY, message_uuid TEXT, transport TEXT, status TEXT, from_email TEXT, from_name TEXT, to_emails TEXT, subject TEXT, text_body TEXT, html_body TEXT, error_class TEXT, error_message TEXT, created_at TEXT, sent_at TEXT, failed_at TEXT)');
    $repository = new EmailLogRepository($pdo);
    $from = new EmailAddress('sender@example.com');
    $to = [new EmailAddress('recipient@example.com')];
    $repository->recordSuccess('one', new EmailMessage($from, $to, 'Password: secret ' . str_repeat('x', 300), 'https://example.com/?token=abc', null));
    $repository->recordFailure('two', new EmailMessage($from, $to, 'Hello', 'Bearer abcdefghijkl', '<p>OTP: 123456</p>'), new RuntimeException('Secret: value'));

    $rows = $repository->search();
    expect($rows[0]['status'])->toBe('failed')
        ->and($rows[0]['text_body'])->toBe('Bearer [redacted]')
        ->and($rows[0]['html_body'])->toBe('<p>OTP: [redacted]</p>')
        ->and($rows[0]['error_message'])->toBe('Secret: [redacted]')
        ->and($rows[1]['subject'])->toBe(mb_substr('Password: [redacted] ' . str_repeat('x', 300), 0, 255))
        ->and($rows[1]['text_body'])->toBe('https://example.com/?token=[redacted]')
        ->and($rows[1]['html_body'])->toBeNull();
});