<?php

declare(strict_types=1);

namespace Zoosper\Admin\Asset;

/**
 * Renders module-owned admin assets as safe HTML tags.
 *
 * This renderer is intentionally small and deterministic so the admin layout can
 * include assets from enabled modules without hard-coding feature CSS/JS files.
 * Asset paths come from static module config only and must never contain runtime
 * secrets such as OTPs, TOTP secrets, recovery codes, payment data or session
 * tokens.
 */
final readonly class AdminAssetRenderer
{
    public function __construct(private AdminAssetRegistry $registry)
    {
    }

    /**
     * Render stylesheet link tags for all enabled module-owned admin styles.
     */
    public function renderStylesheets(): string
    {
        $html = '';
        foreach ($this->registry->stylesheets() as $asset) {
            $html .= '<link rel="stylesheet" href="' . $this->escape($asset->path) . '">' . PHP_EOL;
        }

        return $html;
    }

    /**
     * Render script tags for all enabled module-owned admin scripts.
     */
    public function renderScripts(): string
    {
        $html = '';
        foreach ($this->registry->scripts() as $asset) {
            $html .= '<script src="' . $this->escape($asset->path) . '"' . ($asset->defer ? ' defer' : '') . '></script>' . PHP_EOL;
        }

        return $html;
    }

    /**
     * Escape an asset path before rendering it into HTML.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
