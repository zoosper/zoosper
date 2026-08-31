<?php

declare(strict_types=1);

namespace Zoosper\Settings\Admin;

use Zoosper\Core\Url\AdminUrlGenerator;

/** Canonical Settings Admin URLs shared by screen and mutation responders. */
final readonly class SettingsAdminUrls
{
    public function __construct(private ?AdminUrlGenerator $adminUrls = null)
    {
    }

    /** @param array<string, scalar|null> $query */
    public function url(string $path = '', array $query = []): string
    {
        if ($this->adminUrls !== null) {
            return $this->adminUrls->url($path, $query);
        }

        $url = '/admin/' . ltrim($path, '/');
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    public function scope(string $type, string $key): string
    {
        return $type === 'default'
            ? $this->url('settings')
            : $this->url('settings', ['scope' => $type, 'scope_key' => $key]);
    }
}










