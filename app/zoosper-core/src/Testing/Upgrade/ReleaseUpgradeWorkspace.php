<?php

declare(strict_types=1);

namespace Zoosper\Core\Testing\Upgrade;

use RuntimeException;

/**
 * Creates disposable Git worktrees for release-upgrade tests without changing
 * the caller's branch, .env, database, Media storage, or compiled cache.
 */
final class ReleaseUpgradeWorkspace
{
    private ?string $path = null;

    public function __construct(private readonly string $repository)
    {
    }

    public function create(string $reference): string
    {
        if ($this->path !== null) {
            throw new RuntimeException('Release upgrade workspace has already been created.');
        }
        if (preg_match('/^[A-Za-z0-9._\/-]+$/', $reference) !== 1) {
            throw new RuntimeException('Unsafe Git reference for release upgrade workspace.');
        }
        $path = sys_get_temp_dir() . '/zoosper-upgrade-' . bin2hex(random_bytes(8));
        $command = sprintf(
            'git -C %s worktree add --detach %s %s 2>&1',
            escapeshellarg($this->repository),
            escapeshellarg($path),
            escapeshellarg($reference),
        );
        exec($command, $output, $code);
        if ($code !== 0) {
            throw new RuntimeException('Unable to create release upgrade worktree: ' . implode("\n", $output));
        }
        $this->path = $path;
        return $path;
    }

    public function remove(): void
    {
        if ($this->path === null) {
            return;
        }
        $command = sprintf(
            'git -C %s worktree remove --force %s 2>&1',
            escapeshellarg($this->repository),
            escapeshellarg($this->path),
        );
        exec($command, $output, $code);
        if ($code !== 0) {
            throw new RuntimeException('Unable to remove release upgrade worktree: ' . implode("\n", $output));
        }
        $this->path = null;
    }

    public function __destruct()
    {
        if ($this->path !== null) {
            exec(sprintf(
                'git -C %s worktree remove --force %s >/dev/null 2>&1',
                escapeshellarg($this->repository),
                escapeshellarg($this->path),
            ));
        }
    }
}
