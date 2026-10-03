<?php
declare(strict_types=1);
namespace Zoosper\TwoFactor\Challenge;
use PDO;
/** Atomically records a user's newest accepted TOTP time-step. */
final readonly class AdminTotpReplayRepository
{
    /** @psalm-suppress PossiblyUnusedMethod Constructed through the module service manifest. */
    public function __construct(private PDO $pdo)
    {
    }
    public function claimIfNewer(int $adminUserId, int $counter, string $usedAt): bool
    {
        if ($adminUserId <= 0 || $counter < 0) {
            return false;
        }
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sqlite = is_string($driver) && strtolower($driver) === 'sqlite';
        $insertSql = $sqlite
            ? 'INSERT OR IGNORE INTO admin_totp_replay_state (admin_user_id,last_counter,updated_at) VALUES (:admin_user_id,:last_counter,:updated_at)'
            : 'INSERT IGNORE INTO admin_totp_replay_state (admin_user_id,last_counter,updated_at) VALUES (:admin_user_id,:last_counter,:updated_at)';
        $insert = $this->pdo->prepare($insertSql);
        $insert->bindValue('admin_user_id', $adminUserId, PDO::PARAM_INT);
        $insert->bindValue('last_counter', $counter, PDO::PARAM_INT);
        $insert->bindValue('updated_at', $usedAt);
        $insert->execute();
        if ($insert->rowCount() === 1) {
            return true;
        }
        $update = $this->pdo->prepare(
            'UPDATE admin_totp_replay_state SET last_counter = :last_counter, updated_at = :updated_at '
            . 'WHERE admin_user_id = :admin_user_id AND last_counter < :comparison_counter'
        );
        $update->bindValue('last_counter', $counter, PDO::PARAM_INT);
        $update->bindValue('updated_at', $usedAt);
        $update->bindValue('admin_user_id', $adminUserId, PDO::PARAM_INT);
        $update->bindValue('comparison_counter', $counter, PDO::PARAM_INT);
        $update->execute();
        return $update->rowCount() === 1;
    }
}
