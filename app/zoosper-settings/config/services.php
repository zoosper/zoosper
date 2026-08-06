<?php

declare(strict_types=1);

use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Config\Scope\ScopeConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Settings\Audit\SettingsAuditLogger;
use Zoosper\Settings\Catalogue\ModuleSettingsCatalogueLoader;
use Zoosper\Settings\Value\ScopedSettingValueResolver;
use Zoosper\Settings\Value\SettingValueResolver;
use Zoosper\Settings\Write\SectionSettingsWriter;
use Zoosper\Settings\Write\ScopedSettingClearer;
use Zoosper\Settings\Write\SettingValueNormaliser;

return [
    ModuleSettingsCatalogueLoader::class => static fn (ServiceContainer $services): ModuleSettingsCatalogueLoader => new ModuleSettingsCatalogueLoader(
        $services->get(ModuleRegistry::class),
    ),
    SettingValueResolver::class => static fn (ServiceContainer $services): SettingValueResolver => new SettingValueResolver(
        $services->get(ConfigRepository::class),
    ),
    ScopeConfigRepository::class => static fn (ServiceContainer $services): ScopeConfigRepository => new ScopeConfigRepository(
        $services->get(PDO::class),
    ),
    ScopedSettingValueResolver::class => static fn (ServiceContainer $services): ScopedSettingValueResolver => new ScopedSettingValueResolver(
        $services->get(ScopeConfigRepository::class),
        $services->get(SettingValueResolver::class),
    ),
    SettingValueNormaliser::class => static fn (): SettingValueNormaliser => new SettingValueNormaliser(),
    SectionSettingsWriter::class => static fn (ServiceContainer $services): SectionSettingsWriter => new SectionSettingsWriter(
        $services->get(PDO::class),
        $services->get(ScopeConfigRepository::class),
        $services->get(SettingValueNormaliser::class),
    ),
    ScopedSettingClearer::class => static fn (ServiceContainer $services): ScopedSettingClearer => new ScopedSettingClearer(
        $services->get(ScopeConfigRepository::class),
    ),
    SettingsAuditLogger::class => static fn (ServiceContainer $services): SettingsAuditLogger => new SettingsAuditLogger(
        $services->has(AuditLoggerInterface::class) ? $services->get(AuditLoggerInterface::class) : null,
    ),
];
