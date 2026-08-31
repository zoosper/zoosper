<?php

declare(strict_types=1);

namespace Zoosper\Mail\Tests\Unit\Schema;

test('email log bodies use the longtext schema type', function (): void {
    $basePath = dirname(__DIR__, 5);
    $schema = require $basePath . '/app/zoosper-mail/config/db_schema.php';
    $columns = $schema['tables']['smtp_email_log']['columns'] ?? [];

    expect($columns['text_body']['type'] ?? null)->toBe('longtext');
    expect($columns['html_body']['type'] ?? null)->toBe('longtext');
    expect($columns['error_message']['type'] ?? null)->toBe('text');
});

test('email body expansion migration is safe on sqlite', function (): void {
    $basePath = dirname(__DIR__, 5);
    $migration = require $basePath
        . '/app/zoosper-mail/database/migrations/202607310001_expand_smtp_email_log_bodies.php';
    $pdo = new \PDO('sqlite::memory:');

    expect(is_callable($migration))->toBeTrue();

    $migration($pdo, 'sqlite');

    expect(true)->toBeTrue();
});










