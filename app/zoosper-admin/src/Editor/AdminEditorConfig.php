<?php

declare(strict_types=1);

namespace Zoosper\Admin\Editor;

use Zoosper\Core\Config\ConfigRepository;

/**
 * Reads admin editor configuration for large content fields.
 *
 * The editor can be disabled, shown on demand, or backed by a configured
 * provider. This keeps controllers independent from a specific WYSIWYG library
 * and allows deployments to run textarea-only mode when required.
 */
final readonly class AdminEditorConfig
{
    public function __construct(private ConfigRepository $config)
    {
    }

    /**
     * Return true when a rich editor should be available in admin forms.
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->config->get('editor.enabled', false) ?? false);
    }

    /**
     * Return the configured editor provider key.
     */
    public function provider(): string
    {
        return (string) ($this->config->get('editor.provider', 'editorjs') ?? 'editorjs');
    }

    /**
     * Return true when the admin UI should allow show/hide editor switching.
     */
    public function allowToggle(): bool
    {
        return (bool) ($this->config->get('editor.allow_toggle', true) ?? true);
    }

    /**
     * Return the configured persisted content format.
     */
    public function storeFormat(): string
    {
        return (string) ($this->config->get('editor.store_format', 'json') ?? 'json');
    }
}
