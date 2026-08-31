<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Lifecycle;

use InvalidArgumentException;
use LogicException;

/** Explicit policy registry with loud duplicate and missing-policy failures. */
final class EntityLifecyclePolicyRegistry
{
    /** @var array<string, EntityLifecyclePolicyInterface> */
    private array $policies = [];

    public function register(EntityLifecyclePolicyInterface $policy): void
    {
        $type = trim($policy->entityType());
        if ($type === '') {
            throw new InvalidArgumentException('Lifecycle policy entity type must not be empty.');
        }

        if (isset($this->policies[$type])) {
            throw new LogicException(sprintf('Lifecycle policy for entity type "%s" is already registered.', $type));
        }

        $this->policies[$type] = $policy;
    }

    public function has(string $entityType): bool
    {
        return isset($this->policies[$entityType]);
    }

    public function get(string $entityType): EntityLifecyclePolicyInterface
    {
        return $this->policies[$entityType] ?? throw new LogicException(sprintf(
            'No lifecycle policy is registered for entity type "%s". Register a policy before exposing archive, disable, or delete operations.',
            $entityType,
        ));
    }

    /** @return list<string> */
    public function entityTypes(): array
    {
        $types = array_keys($this->policies);
        sort($types);

        return $types;
    }
}










