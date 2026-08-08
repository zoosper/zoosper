<?php

declare(strict_types=1);

use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Settings\Admin\SettingsAdminUrls;
use Zoosper\Settings\Admin\SettingsCatalogueResponder;
use Zoosper\Settings\Admin\SettingsMutationCoordinator;
use Zoosper\Settings\Admin\SettingsPresentationBuilder;
use Zoosper\Settings\Audit\SettingsAuditLogger;
use Zoosper\Settings\Catalogue\ModuleSettingsCatalogueLoader;
use Zoosper\Settings\Controller\SettingsCatalogueController;
use Zoosper\Settings\Scope\SettingsScopeSelection;
use Zoosper\Settings\Value\ScopedSettingValueResolver;
use Zoosper\Settings\Write\ScopedSettingClearer;
use Zoosper\Settings\Write\SectionSettingsWriter;

return [
    SettingsCatalogueController::class => static function (ServiceContainer $services): SettingsCatalogueController {
        $scopeSelection = $services->get(SettingsScopeSelection::class);
        $urls = $services->get(SettingsAdminUrls::class);

        return new SettingsCatalogueController(
            $services->get(SessionGuard::class),
            new SettingsCatalogueResponder(
                $services->get(AdminViewRendererInterface::class),
                $services->get(ModuleSettingsCatalogueLoader::class),
                $services->get(ScopedSettingValueResolver::class),
                $scopeSelection,
                $services->get(CsrfTokenManager::class),
                $urls,
                $services->get(SettingsPresentationBuilder::class),
            ),
            new SettingsMutationCoordinator(
                $services->get(ModuleSettingsCatalogueLoader::class),
                $services->get(ScopedSettingValueResolver::class),
                $scopeSelection,
                $services->get(SectionSettingsWriter::class),
                $services->get(ScopedSettingClearer::class),
                $services->get(SettingsAuditLogger::class),
                $services->get(FlashMessageStoreInterface::class),
                $urls,
            ),
        );
    },
];
