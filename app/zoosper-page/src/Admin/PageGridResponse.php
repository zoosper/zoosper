<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/** Framework-neutral response payload for thin Page controller adapters. */
final readonly class PageGridResponse
{
    /** @param array<string, string> $headers */
    public function __construct(
        public int $status,
        public array $headers,
        public string $body,
    ) {
        if ($status < 100 || $status > 599) {
            throw new \InvalidArgumentException('Invalid HTTP response status.');
        }
    }

    public static function html(string $body): self
    {
        return new self(200, ['Content-Type' => 'text/html; charset=UTF-8'], $body);
    }

    public static function redirect(string $path): self
    {
        if ($path === '' || $path[0] !== '/' || str_contains($path, '://')) {
            throw new \InvalidArgumentException('Redirect must use a local application path.');
        }

        return new self(303, ['Location' => $path], '');
    }
}










