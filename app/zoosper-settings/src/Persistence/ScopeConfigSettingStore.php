<?php

declare(strict_types=1);

namespace Zoosper\Settings\Persistence;

use PDO;
use Throwable;
use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\ScopedConfig\ScopeType;

final readonly class ScopeConfigSettingStore implements ScopedSettingStoreInterface
{
    public function __construct(private PDO $pdo, private ScopeConfigRepository $repository)
    {
    }

    public function resolve(string $path, ScopeContext $context): array
    {
        return $this->repository->getWithSource($path, $context);
    }

    public function writeMany(array $values, ScopeType $scopeType, ?string $scopeKey): void
    {
        $this->pdo->beginTransaction();
        try {
            foreach ($values as $path => $value) {
                $this->repository->set($path, $scopeType, $scopeKey, $value);
            }
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function clear(string $path, ScopeType $scopeType, ?string $scopeKey): void
    {
        $this->repository->clear($path, $scopeType, $scopeKey);
    }
}










