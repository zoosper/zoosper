<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

use InvalidArgumentException;

/**
 * Registers Zoosper i18n/translation services with the application container.
 *
 * The current Zoosper container exposes `factory()` for lazy services, so this
 * provider prefers that method when available. It still supports common
 * container methods used by third-party integrations (`singleton`, `bind`,
 * `set`, `instance`) without coupling the i18n package to one concrete
 * container implementation too early.
 */
final readonly class I18nServiceProvider
{
    /** @param array<string, mixed> $i18nConfig */
    public function __construct(
        private string $basePath,
        private array $i18nConfig = [],
    ) {
    }

    public function register(object $container): void
    {
        $this->registerService($container, LocaleResolverInterface::class, fn (): LocaleResolverInterface => new ConfiguredLocaleResolver($this->i18nConfig));
        $this->registerService($container, ConfiguredLocaleResolver::class, fn (): ConfiguredLocaleResolver => new ConfiguredLocaleResolver($this->i18nConfig));
        $this->registerService($container, TranslationFileAggregator::class, fn (): TranslationFileAggregator => new TranslationFileAggregator($this->basePath));
        $this->registerService($container, TranslationResolver::class, fn (): TranslationResolver => new TranslationResolver($this->basePath));
        $this->registerService($container, AdminTranslatorResolver::class, fn (): AdminTranslatorResolver => new AdminTranslatorResolver($this->basePath, $this->i18nConfig));
        $this->registerService($container, TranslatorInterface::class, fn (): TranslatorInterface => (new AdminTranslatorResolver($this->basePath, $this->i18nConfig))->resolve());
    }

    private function registerService(object $container, string $id, callable $factory): void
    {
        if (method_exists($container, 'factory')) {
            $container->factory($id, $factory);

            return;
        }

        if (method_exists($container, 'singleton')) {
            $container->singleton($id, $factory);

            return;
        }

        if (method_exists($container, 'bind')) {
            $container->bind($id, $factory);

            return;
        }

        if (method_exists($container, 'set')) {
            $container->set($id, $factory());

            return;
        }

        if (method_exists($container, 'instance')) {
            $container->instance($id, $factory());

            return;
        }

        throw new InvalidArgumentException('Unsupported container. Expected factory(), singleton(), bind(), set() or instance().');
    }
}
