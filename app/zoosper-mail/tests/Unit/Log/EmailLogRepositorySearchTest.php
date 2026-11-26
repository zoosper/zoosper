<?php

declare(strict_types=1);

use Zoosper\Mail\Log\EmailLogRepository;

final class EmailLogNativePlaceholderCheckingPdo extends PDO
{
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        preg_match_all('/:([a-z_][a-z0-9_]*)/i', $query, $matches);
        if (count($matches[1]) !== count(array_unique($matches[1]))) {
            throw new PDOException('Repeated named placeholder in native prepared statement');
        }

        return parent::prepare($query, $options);
    }
}

it('searches mail logs with optional parameterised filters and bounded limits', function (): void {
    $pdo = new EmailLogNativePlaceholderCheckingPdo('sqlite::memory:');
    $pdo->exec('CREATE TABLE smtp_email_log (id INTEGER PRIMARY KEY, status TEXT, from_email TEXT, to_emails TEXT, subject TEXT)');
    $insert = $pdo->prepare('INSERT INTO smtp_email_log VALUES (?, ?, ?, ?, ?)');
    $insert->execute([1, 'sent', 'alice@example.com', 'bob@example.com', 'Report 0']);
    $insert->execute([2, 'failed', 'eve@example.com', 'alice@example.com', 'Other']);
    $insert->execute([3, '0', 'zero@example.com', 'bob@example.com', 'Report 0']);
    $repository = new EmailLogRepository($pdo);
    $ids = static fn (array $rows): array => array_column($rows, 'id');

    expect($ids($repository->search()))->toBe([3, 2, 1])
        ->and($ids($repository->search(['status' => '', 'email' => '', 'subject' => ''])))->toBe([3, 2, 1])
        ->and($ids($repository->search(['status' => '0'])))->toBe([3])
        ->and($ids($repository->search(['email' => 'alice'])))->toBe([2, 1])
        ->and($ids($repository->search(['email' => 'eve'])))->toBe([2])
        ->and($ids($repository->search(['email' => 'bob'])))->toBe([3, 1])
        ->and($ids($repository->search(['subject' => '0'])))->toBe([3, 1])
        ->and($ids($repository->search(['status' => 'sent', 'email' => 'bob', 'subject' => 'Report'])))->toBe([1])
        ->and($ids($repository->search(['email' => "' OR 1=1 --"])))->toBe([])
        ->and($ids($repository->search([], 0)))->toBe([3]);
});