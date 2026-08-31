<?php

declare(strict_types=1);

it('keeps runtime schema bootstrap SQLite-only and leaves MySQL to declarative schema', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-core/src/Security/RateLimit/DatabaseRateLimitStore.php');
    $schema = (string) file_get_contents($root . '/app/zoosper-core/config/db_schema.php');

    expect($source)
        ->toContain('if (!$this->isSqlite())')
        ->toContain('return;')
        ->toContain('id INTEGER PRIMARY KEY AUTOINCREMENT')
        ->toContain('ON DUPLICATE KEY UPDATE')
        ->and($schema)
        ->toContain("'rate_limit_buckets' => [")
        ->toContain("'rate_limit_buckets_unique_window'")
        ->toContain("'rate_limit_buckets_expires_idx'");
});










