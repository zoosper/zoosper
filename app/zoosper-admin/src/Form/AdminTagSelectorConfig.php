<?php

declare(strict_types=1);

namespace Zoosper\Admin\Form;

use Zoosper\Core\Config\ConfigRepository;

/**
 * Reads admin tag-selector configuration.
 *
 * Tag selectors are used for safe multi-value editing where relying on a native
 * multi-select box would risk editors accidentally losing selections by
 * forgetting modifier keys. The configuration keeps the feature progressive:
 * forms must continue to submit the same field names without JavaScript.
 */
final readonly class AdminTagSelectorConfig
{
    public function __construct(private ConfigRepository $config)
    {
    }

    /**
     * Return true when tag selector progressive enhancement is enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->config->get('tag_selector.enabled', true) ?? true);
    }

    /**
     * Return true when the tag selector should expose search UI.
     */
    public function allowSearch(): bool
    {
        return (bool) ($this->config->get('tag_selector.allow_search', true) ?? true);
    }

    /**
     * Return true when editors can clear selected tags in one action.
     */
    public function allowClear(): bool
    {
        return (bool) ($this->config->get('tag_selector.allow_clear', true) ?? true);
    }

    /**
     * Return the maximum number of visible options before search becomes vital.
     */
    public function maxVisibleOptions(): int
    {
        return max(5, (int) ($this->config->get('tag_selector.max_visible_options', 25) ?? 25));
    }
}
