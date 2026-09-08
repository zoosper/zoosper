<?php

declare(strict_types=1);

use Zoosper\Core\Testing\Upgrade\ReleaseUpgradeWorkspace;

it('creates and removes an immutable detached upgrade worktree', function (): void {
    $root = dirname(__DIR__, 5);
    $head = trim((string) shell_exec('git -C ' . escapeshellarg($root) . ' rev-parse HEAD'));
    $statusBefore = (string) shell_exec('git -C ' . escapeshellarg($root) . ' status --short');
    $workspace = new ReleaseUpgradeWorkspace($root);
    $path = $workspace->create('v0.3.1-alpha.1');

    expect($path)->toBeDirectory()
        ->and(trim((string) shell_exec('git -C ' . escapeshellarg($path) . ' rev-parse HEAD')))
        ->toBe(trim((string) shell_exec('git -C ' . escapeshellarg($root) . ' rev-list -n 1 v0.3.1-alpha.1')))
        ->and(trim((string) shell_exec('git -C ' . escapeshellarg($path) . ' branch --show-current')))->toBe('');

    $workspace->remove();
    expect($path)->not->toBeDirectory()
        ->and(trim((string) shell_exec('git -C ' . escapeshellarg($root) . ' rev-parse HEAD')))->toBe($head)
        ->and((string) shell_exec('git -C ' . escapeshellarg($root) . ' status --short'))->toBe($statusBefore);
});

it('rejects unsafe references before executing Git', function (): void {
    $workspace = new ReleaseUpgradeWorkspace(dirname(__DIR__, 5));
    expect(fn (): string => $workspace->create('v0.3.1-alpha.1; touch /tmp/no'))
        ->toThrow(RuntimeException::class, 'Unsafe Git reference');
});
