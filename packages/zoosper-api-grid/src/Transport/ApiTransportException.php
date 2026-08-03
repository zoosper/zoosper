<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Transport;

use RuntimeException;
use Throwable;

final class ApiTransportException extends RuntimeException
{
    public const INITIALISATION = 'initialisation';
    public const NETWORK = 'network';
    public const TIMEOUT = 'timeout';
    public const RESPONSE_TOO_LARGE = 'response_too_large';
    public const INVALID_JSON = 'invalid_json';
    public const INVALID_JSON_ROOT = 'invalid_json_root';
    public const NON_SUCCESS = 'non_success';

    public function __construct(
        string $message,
        public readonly ?string $correlationId = null,
        ?Throwable $previous = null,
        public readonly string $category = self::NETWORK,
        public readonly ?int $statusCode = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
