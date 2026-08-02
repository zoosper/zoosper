<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Transport;

use RuntimeException;
use Throwable;

final class ApiTransportException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $correlationId = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
