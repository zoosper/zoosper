<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Resolves an admin translator using a logged-in admin user's locale context.
 *
 * This service keeps controllers clean by centralising the locale-resolution
 * chain for admin runtime messages:
 *
 * Admin user -> AdminUserLocaleResolver -> LocaleResolution -> Translator.
 */
final readonly class AdminContextTranslatorResolver
{
    public function __construct(
        private AdminUserLocaleResolver $adminUserLocaleResolver,
        private TranslationResolver $translationResolver,
    ) {
    }

    public function resolveForAdminUser(?object $adminUser): TranslatorInterface
    {
        return $this->translationResolver->forResolution(
            $this->adminUserLocaleResolver->resolveForAdminUser($adminUser),
        );
    }
}
