<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Database\ConnectionFactory;

it('forbids SQLite in production when enforce_mysql_in_production is true', function (): void {
    $config = ConfigRepository::fromArray([
        'app' => ['env' => 'production'],
        'database_policy' => ['enforce_mysql_in_production' => true],
        'database' => [
            'default' => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                ],
            ],
        ],
    ]);

    $factory = new ConnectionFactory($config, dirname(__DIR__, 4));

    expect(fn () => $factory->create())
        ->toThrow(RuntimeException::class, 'SQLite database driver is not permitted in production environments');
});

it('permits SQLite in local development environments', function (): void {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $config = ConfigRepository::fromArray([
        'app' => ['env' => 'local'],
        'database_policy' => ['enforce_mysql_in_production' => true],
        'database' => [
            'default' => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                ],
            ],
        ],
    ]);

    $factory = new ConnectionFactory($config, dirname(__DIR__, 4));
    $pdo = $factory->create();

    expect($pdo)->toBeInstanceOf(PDO::class);
});











