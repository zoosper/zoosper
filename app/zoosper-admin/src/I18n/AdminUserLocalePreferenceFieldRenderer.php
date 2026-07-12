<?php

declare(strict_types=1);

namespace Zoosper\Admin\I18n;

use Zoosper\Core\I18n\SupportedLocaleProvider;

/**
 * Renders the admin-user locale preference field.
 *
 * The field is intentionally small and dependency-light so it can be reused by
 * the current admin-user form and by a future profile/preference screen. Locale
 * options come from SupportedLocaleProvider instead of being hard-coded in a
 * controller/template.
 */
final readonly class AdminUserLocalePreferenceFieldRenderer
{
    public function __construct(private SupportedLocaleProvider $supportedLocaleProvider)
    {
    }

    public function render(?string $currentLocale = null): string
    {
        $options = [];
        $options[] = sprintf(
            '<option value=""%s>%s</option>',
            $currentLocale === null || $currentLocale === '' ? ' selected' : '',
            $this->escape('Use configured admin locale'),
        );

        foreach ($this->supportedLocaleProvider->adminLocales() as $code => $label) {
            $options[] = sprintf(
                '<option value="%s"%s>%s</option>',
                $this->escape($code),
                $currentLocale === $code ? ' selected' : '',
                $this->escape($label),
            );
        }

        return implode("\n", [
            '<div class="admin-form-field admin-form-field--locale">',
            '    <label for="admin-user-locale">Admin interface locale</label>',
            '    <select id="admin-user-locale" name="locale">',
            '        ' . implode("\n        ", $options),
            '    </select>',
            '    <small class="admin-form-help">Leave blank to use the configured admin locale.</small>',
            '</div>',
        ]);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
