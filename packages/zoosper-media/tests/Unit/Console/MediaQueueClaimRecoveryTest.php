<?php
declare(strict_types=1);
it('requires atomic single-winner claims and bounded stale recovery', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Console/ProcessMediaQueueCommand.php');
    expect($source)->toContain("WHERE id = :id AND status = 'pending'")
        ->toContain('$statement->rowCount() === 1')
        ->toContain('STALE_AFTER_SECONDS = 900')
        ->toContain("status = 'processing' AND updated_at < :cutoff")
        ->toContain("attempts + 1 >= :max_attempts")
        ->toContain('lost its processing claim');
});
it('allows only one claimant for the same pending row', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec("CREATE TABLE media_processing_queue (id INTEGER PRIMARY KEY, status TEXT, attempts INTEGER, error_message TEXT, started_at TEXT, completed_at TEXT, updated_at TEXT)");
    $pdo->exec("INSERT INTO media_processing_queue VALUES (1,'pending',0,NULL,NULL,NULL,'2026-10-15 00:00:00')");
    $claim = $pdo->prepare("UPDATE media_processing_queue SET status='processing',started_at=:now,updated_at=:now WHERE id=:id AND status='pending'");
    $claim->execute(['now'=>'2026-10-15 00:01:00','id'=>1]); $first=$claim->rowCount();
    $claim->execute(['now'=>'2026-10-15 00:01:00','id'=>1]); $second=$claim->rowCount();
    expect($first)->toBe(1)->and($second)->toBe(0);
});
