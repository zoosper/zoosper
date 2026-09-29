<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Mapping;

use UnexpectedValueException;
use Throwable;

/**
 * Describes externally controlled API response schema drift without retaining payload data.
 *
 * @psalm-api
 */
final class ApiGridResponseMappingException extends UnexpectedValueException
{
    public const SCHEMA_MISMATCH = 'schema_mismatch';

    public function __construct(
        string $message,
/** Stable payload-free category for integrations and diagnostics. */
        public readonly string $category = self::SCHEMA_MISMATCH,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
