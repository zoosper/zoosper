<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Immutable description of a method call that can be passed through plugins.
 */
final readonly class MethodInvocation
{
    /**
     * @param array<int|string, mixed> $arguments
     */
    public function __construct(
        public object|string $subject,
        public string $method,
        public array $arguments = [],
    ) {
    }

    /**
     * @param array<int|string, mixed> $arguments
     */
    public function withArguments(array $arguments): self
    {
        return new self($this->subject, $this->method, $arguments);
    }

    public function subjectKey(): string
    {
        return is_object($this->subject) ? $this->subject::class : $this->subject;
    }

    public function key(): string
    {
        return $this->subjectKey() . '::' . $this->method;
    }
}
