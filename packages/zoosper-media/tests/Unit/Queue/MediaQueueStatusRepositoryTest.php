<?php

declare(strict_types=1);

use Zoosper\Media\Queue\MediaQueueStatusRepository;

it('reports queue depth retries failures and persisted latency', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE media_processing_queue (id INTEGER PRIMARY KEY, status TEXT NOT NULL, attempts INTEGER NOT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL, started_at TEXT NULL, completed_at TEXT NULL)');
    $insert = $pdo->prepare('INSERT INTO media_processing_queue VALUES (:id,:status,:attempts,:created,:updated,:started,:completed)');
    foreach ([
        [1, 'pending', 0, '2026-10-12 09:59:00', '2026-10-12 09:59:00', null, null],
        [2, 'pending', 2, '2026-10-12 09:58:00', '2026-10-12 09:59:30', null, null],
        [3, 'processing', 1, '2026-10-12 09:57:00', '2026-10-12 10:00:00', '2026-10-12 10:00:00', null],
        [4, 'completed', 0, '2026-10-12 09:50:00', '2026-10-12 09:52:30', '2026-10-12 09:52:00', '2026-10-12 09:52:30'],
        [5, 'failed', 3, '2026-10-12 09:40:00', '2026-10-12 09:45:00', '2026-10-12 09:44:00', null],
    ] as [$id,$status,$attempts,$created,$updated,$started,$completed]) {
        $insert->execute(compact('id','status','attempts','created','updated','started','completed'));
    }
    $status = (new MediaQueueStatusRepository($pdo))->inspect(new DateTimeImmutable('2026-10-12 10:00:00 UTC'));
    expect($status->counts)->toBe(['pending' => 2, 'processing' => 1, 'completed' => 1, 'failed' => 1])
        ->and($status->retryPending)->toBe(1)
        ->and($status->totalAttempts)->toBe(6)
        ->and($status->oldestPendingAgeSeconds)->toBe(120)
        ->and($status->averageQueueWaitSeconds)->toBe(180)
        ->and($status->maximumQueueWaitSeconds)->toBe(240)
        ->and($status->averageProcessingSeconds)->toBe(30)
        ->and($status->maximumProcessingSeconds)->toBe(30);
});

it('aggregates persisted durations in SQL without loading every completed row', function (): void {
    $source=(string)file_get_contents(dirname(__DIR__,3).'/src/Queue/MediaQueueStatusRepository.php');
    expect($source)->toContain('AVG({$expression})')->toContain('MAX({$expression})')->not->toContain('SELECT {$startColumn} AS started, {$endColumn} AS ended');
});
