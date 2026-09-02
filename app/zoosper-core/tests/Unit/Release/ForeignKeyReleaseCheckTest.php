<?php
declare(strict_types=1);

use Zoosper\Core\Release\ReleaseCheck;

/** @param array<string, int> $counts */
function foreignKeyReleaseResult(array $counts): object
{
    $result = (new ReleaseCheck(
        dirname(__DIR__, 5),
        static fn (): array => $counts,
    ))->foreignKeyResult();

    if ($result === null) {
        throw new RuntimeException('Foreign-key release result was not emitted.');
    }

    return $result;
}

it('passes only a fully reconciled foreign-key state', function (): void {
    $result = foreignKeyReleaseResult([
        'present' => 33,
        'add' => 0,
        'mismatch' => 0,
        'sqlite_rebuild_required' => 0,
    ]);
    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe('present=33, add=0, mismatch=0, sqlite_rebuild_required=0');
});

it('fails for pending additions mismatches and sqlite rebuild requirements', function (array $counts): void {
    expect(foreignKeyReleaseResult($counts)->passed)->toBeFalse();
})->with([
    'pending additions' => [['present' => 32, 'add' => 1, 'mismatch' => 0, 'sqlite_rebuild_required' => 0]],
    'mismatch' => [['present' => 32, 'add' => 0, 'mismatch' => 1, 'sqlite_rebuild_required' => 0]],
    'sqlite rebuild' => [['present' => 32, 'add' => 0, 'mismatch' => 0, 'sqlite_rebuild_required' => 1]],
]);

it('fails closed when foreign-key inspection throws', function (): void {
    $check = new ReleaseCheck(dirname(__DIR__, 5), static function (): array {
        throw new RuntimeException('database unavailable');
    });
    $result = $check->foreignKeyResult();
    expect($result)->not->toBeNull()
        ->and($result->passed)->toBeFalse()
        ->and($result->message)->toBe('inspection failed: database unavailable');
});
