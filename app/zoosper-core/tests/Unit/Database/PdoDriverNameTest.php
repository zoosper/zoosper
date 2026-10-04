<?php

declare(strict_types=1);

use Zoosper\Core\Database\PdoDriverName;

it('returns the normalised active PDO driver name', function (): void {
    $pdo = new \PDO('sqlite::memory:');

    expect(PdoDriverName::from($pdo))->toBe('sqlite');
});

it('fails closed when PDO does not return a non-empty string driver name', function (mixed $value): void {
    $pdo = new class($value) extends \PDO {
        public function __construct(private readonly mixed $value)
        {
        }

        #[\Override]
        public function getAttribute(int $attribute): mixed
        {
            return $this->value;
        }
    };

    expect(fn (): string => PdoDriverName::from($pdo))
        ->toThrow(\RuntimeException::class, 'PDO did not return a non-empty string driver name.');
})->with([false, null, '', '   ', 123]);
