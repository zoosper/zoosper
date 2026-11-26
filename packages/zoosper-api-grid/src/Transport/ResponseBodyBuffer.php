<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Transport;

/** @internal */
final class ResponseBodyBuffer
{
    private string $body = '';
    private bool $tooLarge = false;

    public function __construct(private readonly int $maximumBytes) {}

    public function append(string $chunk): int
    {
        if (strlen($this->body) + strlen($chunk) > $this->maximumBytes) {
            $this->tooLarge = true;
            return 0;
        }

        $this->body .= $chunk;
        return strlen($chunk);
    }

    public function isTooLarge(): bool
    {
        return $this->tooLarge;
    }

    public function body(): string
    {
        return $this->body;
    }
}