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

it('claims without duplicate named placeholders for native prepares', function (): void {
    $pdo = new class('sqlite::memory:') extends PDO {
        public function prepare(string $query, array $options = []): PDOStatement|false
        {
            preg_match_all('/:([a-z_][a-z0-9_]*)/i', $query, $matches);
            if (count($matches[1]) !== count(array_unique($matches[1]))) {
                throw new RuntimeException('Duplicate named placeholder in prepared statement.');
            }
            return parent::prepare($query, $options);
        }
    };
    $pdo->exec('CREATE TABLE media_processing_queue (id INTEGER PRIMARY KEY, status TEXT, attempts INTEGER, error_message TEXT, started_at TEXT, completed_at TEXT, updated_at TEXT)');
    $pdo->exec("INSERT INTO media_processing_queue VALUES (1, 'pending', 0, NULL, NULL, NULL, '2026-10-15 00:00:00')");
    $command = (new ReflectionClass(\Zoosper\Media\Console\ProcessMediaQueueCommand::class))->newInstanceWithoutConstructor();
    (new ReflectionProperty($command, 'pdo'))->setValue($command, $pdo);

    expect((new ReflectionMethod($command, 'claim'))->invoke($command, 1, '2026-10-15 00:01:00', 0))->toBeTrue();
    $row = $pdo->query('SELECT started_at, updated_at FROM media_processing_queue WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
    expect($row)->toMatchArray(['started_at' => '2026-10-15 00:01:00', 'updated_at' => '2026-10-15 00:01:00']);
});

it('fences expired workers and stale pending snapshots by attempt generation', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE TABLE media_processing_queue (id INTEGER PRIMARY KEY, status TEXT, attempts INTEGER, error_message TEXT, started_at TEXT, completed_at TEXT, updated_at TEXT)");
    $pdo->exec("INSERT INTO media_processing_queue VALUES (1, 'pending', 0, NULL, NULL, NULL, '2026-10-15 00:00:00')");
    $command = (new ReflectionClass(\Zoosper\Media\Console\ProcessMediaQueueCommand::class))->newInstanceWithoutConstructor();
    (new ReflectionProperty($command, 'pdo'))->setValue($command, $pdo);
    $claim = new ReflectionMethod($command, 'claim');
    $recover = new ReflectionMethod($command, 'recoverStaleClaims');
    $finish = new ReflectionMethod($command, 'finish');

    expect($claim->invoke($command, 1, '2026-10-15 00:00:00', 0))->toBeTrue();
    $recover->invoke($command, '2026-10-15 00:16:00');
    expect($claim->invoke($command, 1, '2026-10-15 00:16:00', 0))->toBeFalse();
    expect($claim->invoke($command, 1, '2026-10-15 00:16:00', 1))->toBeTrue();

    foreach (['completed', 'pending', 'failed'] as $status) {
        try {
            $finish->invoke($command, 1, $status, $status === 'completed' ? null : 'old failure', null, 0);
            test()->fail('An expired worker changed a newer claim.');
        } catch (RuntimeException $exception) {
            expect($exception->getMessage())->toContain('lost its processing claim');
        }
        $row = $pdo->query('SELECT status, attempts, error_message FROM media_processing_queue WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        expect($row)->toMatchArray(['status' => 'processing', 'attempts' => 1, 'error_message' => null]);
    }
    $finish->invoke($command, 1, 'completed', null, '2026-10-15 00:17:00', 1);
    expect($pdo->query('SELECT status FROM media_processing_queue WHERE id = 1')->fetchColumn())->toBe('completed');
});
