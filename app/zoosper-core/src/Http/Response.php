<?php

declare(strict_types=1);

namespace Zoosper\Core\Http;

final readonly class Response
{
    /**
     * @param array<string, string> $headers
     */
    private function __construct(
        private string $body,
        private int $statusCode = 200,
        private array $headers = [],
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
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

    public static function redirect(string $location, int $statusCode = 302): self
    {
        return new self('', $statusCode, ['Location' => $location]);
    }

    /**
     * Build a response with arbitrary status/headers/body, exactly as given.
     *
     * Phase C1 (module asset pipeline): AssetController::serve() already
     * computes the correct status/headers (Content-Type per file extension,
     * ETag, Last-Modified, Cache-Control, and a bodyless 304 for a matching
     * If-None-Match) — this factory exposes the existing private constructor
     * under a descriptive name so that array can be adapted into a real
     * Response without guessing at or duplicating that logic. Additive only:
     * json()/html()/redirect() are unchanged.
     *
     * @param array<string, string> $headers
     */
    public static function raw(string $body, int $statusCode = 200, array $headers = []): self
    {
        return new self($body, $statusCode, $headers);
    }

    /**
     * ADDITIVE (2026-07-31, page-cache decorator): read accessors, needed so
     * a decorator (Zoosper\Core\Routing\CachingFallbackHandler) can inspect
     * an already-built Response to decide whether it's cacheable, and to
     * actually serialize it for storage. Purely additive — no existing
     * constructor, factory method, or behaviour changes. `final readonly`
     * describes the class (cannot be subclassed) and its properties
     * (cannot be reassigned after construction); it does not restrict
     * adding new public read-only accessor methods.
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function body(): string
    {
        return $this->body;
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
