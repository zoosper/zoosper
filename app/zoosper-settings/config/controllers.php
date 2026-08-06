<?php

declare(strict_types=1);

use Zoosper\Admin\Message\FlashMessageStoreInterface;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Settings\Catalogue\ModuleSettingsCatalogueLoader;
use Zoosper\Settings\Audit\SettingsAuditLogger;
use Zoosper\Settings\Controller\SettingsCatalogueController;
use Zoosper\Settings\Scope\SettingsScopeSelection;
use Zoosper\Site\Repository\SiteRepository;
use Zoosper\Settings\Value\ScopedSettingValueResolver;
use Zoosper\Settings\Write\SectionSettingsWriter;
use Zoosper\Settings\Write\ScopedSettingClearer;

return [
    SettingsCatalogueController::class => static fn (ServiceContainer $services): SettingsCatalogueController => new SettingsCatalogueController(
        $services->get(SessionGuard::class),
        $services->get(AdminViewRendererInterface::class),
        $services->get(ModuleSettingsCatalogueLoader::class),
        $services->get(ScopedSettingValueResolver::class),
        new SettingsScopeSelection($services->get(SiteRepository::class)),
        $services->get(SectionSettingsWriter::class),
        $services->get(ScopedSettingClearer::class),
        $services->get(SettingsAuditLogger::class),
        $services->get(CsrfTokenManager::class),
        $services->get(FlashMessageStoreInterface::class),
    ),
];
