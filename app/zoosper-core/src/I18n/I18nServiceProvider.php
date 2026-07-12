<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

use InvalidArgumentException;

/**
 * Registers Zoosper i18n/translation services with a container-like object.
 *
 * The provider intentionally supports a small set of common container methods
 * (`set`, `singleton`, `bind`, `instance`) so it can bridge the current Marko
 * container implementation without coupling the i18n module to a concrete
 * container class too early.
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
        if (method_exists($container, 'set')) {
            $container->set($id, $factory);

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

        if (method_exists($container, 'instance')) {
            $container->instance($id, $factory());

            return;
        }

        throw new InvalidArgumentException('Unsupported container. Expected set(), singleton(), bind() or instance().');
    }
}
