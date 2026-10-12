<?php

declare(strict_types=1);

use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Media\Console\MediaQueueStatusCommand;
use Zoosper\Media\Queue\MediaQueueStatusRepository;

it('publishes text and machine-readable queue telemetry', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE media_processing_queue (id INTEGER PRIMARY KEY, status TEXT, attempts INTEGER, created_at TEXT, updated_at TEXT, started_at TEXT NULL, completed_at TEXT NULL)');
    $stdout = fopen('php://memory', 'w+'); $stderr = fopen('php://memory', 'w+');
    $command = new MediaQueueStatusCommand(new MediaQueueStatusRepository($pdo));
    expect($command->run([], new ConsoleOutput($stdout, $stderr)))->toBe(0);
    rewind($stdout); expect(stream_get_contents($stdout))->toContain('pending=0')->toContain('Processing latency: average=n/a maximum=n/a');
    ftruncate($stdout, 0); rewind($stdout);
    expect($command->run(['--format=json'], new ConsoleOutput($stdout, $stderr)))->toBe(0);
    rewind($stdout); $json = json_decode((string) stream_get_contents($stdout), true, 512, JSON_THROW_ON_ERROR);
    expect($json['counts']['failed'])->toBe(0)->and($json['queueWaitSeconds']['average'])->toBeNull();
});
