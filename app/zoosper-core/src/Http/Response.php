<?php
declare(strict_types=1);

namespace Zoosper\Core\Http;
final readonly class Response
{
    private function __construct(private string $body, private int $statusCode = 200, private array $headers = [])
    {
    }

    public static function json(array $payload, int $statusCode = 200): self
    {
        return new self(json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), $statusCode, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    public static function html(string $html, int $statusCode = 200): self
    {
        return new self($html, $statusCode, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function redirect(string $location, int $statusCode = 302): self
    {
        return new self('', $statusCode, ['Location' => $location]);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $n => $v) header($n . ': ' . $v);
        echo $this->body;
    }
}
