<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Lifecycle;

use InvalidArgumentException;

/** One descriptive reason an entity lifecycle operation cannot proceed. */
final readonly class EntityLifecycleBlocker
{
    public function __construct(
        public string $code,
        public string $message,
        public ?string $remediation = null,
        public ?string $referenceType = null,
        public ?int $referenceCount = null,
    ) {
        if (trim($code) === '' || trim($message) === '') {
            throw new InvalidArgumentException('Lifecycle blocker code and message are required.');
        }

        if ($referenceCount !== null && $referenceCount < 0) {
            throw new InvalidArgumentException('Lifecycle blocker reference count cannot be negative.');
        }
    }
}










