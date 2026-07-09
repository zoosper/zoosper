<?php
declare(strict_types=1);

namespace Zoosper\Core\Security;
final readonly class SecurityHeaders
{
    public function __construct(private array $headers)
    {
    }

    public function apply(): void
    {
        foreach ($this->headers as $n => $v) header($n . ': ' . $v);
    }
}
