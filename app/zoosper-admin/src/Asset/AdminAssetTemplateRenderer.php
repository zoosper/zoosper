<?php

declare(strict_types=1);

namespace Zoosper\Admin\Asset;

/**
 * Converts module-owned admin assets into escaped HTML tags.
 *
 * This renderer is useful when the current admin layout renders simple string
 * fragments instead of passing objects into a template partial. Asset paths are
 * escaped before output and must come only from trusted module configuration.
 */
final readonly class AdminAssetTemplateRenderer
{
    public function __construct(private AdminAssetRegistry $registry)
    {
    }

    /**
     * Render stylesheet tags for the admin layout head section.
     */
    public function stylesHtml(): string
    {
        $html = '';
        foreach ($this->registry->stylesheets() as $asset) {
            $html .= '<link rel="stylesheet" href="' . $this->escape($asset->path) . '">' . PHP_EOL;
        }

        return $html;
    }

    /**
     * Render script tags for the admin layout footer/body end section.
     */
    public function scriptsHtml(): string
    {
        $html = '';
        foreach ($this->registry->scripts() as $asset) {
            $html .= '<script src="' . $this->escape($asset->path) . '"' . ($asset->defer ? ' defer' : '') . '></script>' . PHP_EOL;
        }

        return $html;
    }

    /**
     * Escape an asset path before injecting it into HTML.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
