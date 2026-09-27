<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Console;

use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Console\PruneRateLimitBucketsCommand;
use Zoosper\Core\Security\RateLimit\RateLimitStoreMaintenanceInterface;

it('prunes through the maintenance-only contract', function (): void {
    $maintenance = new class implements RateLimitStoreMaintenanceInterface {
        public int $now = 0;
        public function deleteExpired(int $now): int { $this->now = $now; return 7; }
    };
    $stdout = fopen('php://memory', 'w+');
    $stderr = fopen('php://memory', 'w+');
    $command = new PruneRateLimitBucketsCommand($maintenance);

    expect($command->name())->toBe('rate-limit:prune')
        ->and($command->run([], new ConsoleOutput($stdout, $stderr)))->toBe(0)
        ->and($maintenance->now)->toBeGreaterThan(0);
});

it('rejects unexpected arguments before pruning', function (): void {
    $maintenance = new class implements RateLimitStoreMaintenanceInterface {
        public int $calls = 0;
        public function deleteExpired(int $now): int { ++$this->calls; return 0; }
    };
    $stdout = fopen('php://memory', 'w+');
    $stderr = fopen('php://memory', 'w+');

    expect((new PruneRateLimitBucketsCommand($maintenance))->run(['unexpected'], new ConsoleOutput($stdout, $stderr)))->toBe(1)
        ->and($maintenance->calls)->toBe(0);
});
