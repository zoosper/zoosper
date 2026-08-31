<?php

declare(strict_types=1);

return static function (\PDO $pdo, string $driver): void {
    if ($driver !== 'mysql') {
        // SQLite TEXT has no 64 KB storage class limit, so no alteration is needed.
        return;
    }

    $statement = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables '
        . 'WHERE table_schema = DATABASE() AND table_name = :table_name',
    );
    $statement->execute(['table_name' => 'smtp_email_log']);

    if ((int) $statement->fetchColumn() === 0) {
        // On a fresh install the declarative schema creates LONGTEXT directly.
        return;
    }

    $pdo->exec(
        'ALTER TABLE smtp_email_log '
        . 'MODIFY text_body LONGTEXT NULL, '
        . 'MODIFY html_body LONGTEXT NULL',
    );
};










