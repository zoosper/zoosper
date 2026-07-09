<?php

declare(strict_types=1);

namespace Zoosper\Core\Http;

final readonly class Response
{
    /** @param array<string, string> $headers */
    private function __construct(
        private string $body,
        private int $statusCode = 200,
        private array $headers = [],
    ) {
    }

    /** @param array<string, mixed> $payload */
    public static function json(array $payload, int $statusCode = 200): self
    {
        return new self(
            body: json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            statusCode: $statusCode,
            headers: ['Content-Type' => 'application/json; charset=utf-8'],
        );
    }

    public static function html(string $html, int $statusCode = 200): self
    {
        return new self(
            body: $html,
            statusCode: $statusCode,
            headers: ['Content-Type' => 'text/html; charset=utf-8'],
        );
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
