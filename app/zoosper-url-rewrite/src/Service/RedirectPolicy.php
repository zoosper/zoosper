<?php

declare(strict_types=1);

namespace Zoosper\UrlRewrite\Service;

use InvalidArgumentException;

final readonly class RedirectPolicy
{
    /** @return array{source:string,target:string,type:int} */
    public function validate(string $source, string $target, int $type): array
    {
        $source = $this->source($source);
        $target = $this->target($target);

        if (!in_array($type, [301, 302], true)) {
            throw new InvalidArgumentException('Redirect type must be 301 or 302.');
        }
        if ($source === $target) {
            throw new InvalidArgumentException('Redirect source and target must differ.');
        }
        foreach (['/admin', '/api', '/assets', '/static', '/sitemap.xml', '/robots.txt'] as $prefix) {
            if ($source === $prefix || str_starts_with($source, $prefix . '/')) {
                throw new InvalidArgumentException('Redirect source targets a reserved application path.');
            }
        }

        return ['source' => $source, 'target' => $target, 'type' => $type];
    }

    public function source(string $path): string
    {
        $parsedPath = parse_url(trim($path), PHP_URL_PATH);
        if (!is_string($parsedPath)) {
            throw new InvalidArgumentException('Redirect source must contain a valid path.');
        }

        $path = '/' . trim($parsedPath, '/');
        if ($path === '/') {
            throw new InvalidArgumentException('The Site root cannot be used as a redirect source.');
        }

        return $path;
    }

    public function target(string $target): string
    {
        $target = trim($target);
        if ($target === '') {
            throw new InvalidArgumentException('Redirect target is required.');
        }

        $parsedScheme = parse_url($target, PHP_URL_SCHEME);
        if ($parsedScheme === false) {
            throw new InvalidArgumentException('Redirect target must be a valid URL or site-relative path.');
        }
        $scheme = is_string($parsedScheme) ? strtolower($parsedScheme) : '';
        if ($scheme !== '' && !in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Absolute redirect targets must use HTTP or HTTPS.');
        }
        if ($scheme !== '') {
            $parts = parse_url($target);
            $host = is_array($parts) ? ($parts['host'] ?? null) : null;
            if (filter_var($target, FILTER_VALIDATE_URL) === false
                || !is_string($host)
                || trim($host) === '') {
                throw new InvalidArgumentException('Absolute redirect targets must include a valid host.');
            }

            return $target;
        }

        $parsedPath = parse_url($target, PHP_URL_PATH);
        if (!is_string($parsedPath) || $parsedPath === '') {
            throw new InvalidArgumentException('Relative redirect targets must contain a path.');
        }

        return '/' . trim($parsedPath, '/');
    }
}
