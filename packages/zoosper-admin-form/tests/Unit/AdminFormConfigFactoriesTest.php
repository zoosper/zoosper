<?php

declare(strict_types=1);

use Zoosper\AdminForm\AdminFormConfigProviderFactory;
use Zoosper\AdminForm\AdminFormProcessingResult;
use Zoosper\AdminForm\AdminFormProcessorConfigFactory;
use Zoosper\AdminForm\AdminFormProcessorInterface;
use Zoosper\AdminForm\AdminFormSectionProviderInterface;

final class ConfigFactoryTestProcessor implements AdminFormProcessorInterface
{
    public function formHandle(): string
    {
        return 'example';
    }

    public function process(array $form, array $context = []): AdminFormProcessingResult
    {
        return AdminFormProcessingResult::success(['processed' => true]);
    }
}

final class ConfigFactoryTestProvider implements AdminFormSectionProviderInterface
{
    public static int $instances = 0;

    public function __construct()
    {
        self::$instances++;
    }

    public function formHandle(): string
    {
        return 'example';
    }

    public function sections(array $context): iterable
    {
        return [];
    }
}

it('normalises processor configuration and uses fallback only when empty', function (): void {
    $factory = new AdminFormProcessorConfigFactory();
    $fallback = ['example' => [ConfigFactoryTestProcessor::class]];
    $registry = $factory->create(['processors' => ['example' => [null, '', ConfigFactoryTestProcessor::class]]]);
    expect($registry->process('example', [])->payload)->toBe(['processed' => true]);
    expect($registry->process('other', [])->payload)->toBe([]);
    expect($factory->create(['processors' => ['example' => [false, '']]], $fallback)->process('example', [])->payload)
        ->toBe(['processed' => true]);
    expect($factory->create(['processors' => ['other' => []]], $fallback)->process('example', [])->payload)
        ->toBe(['processed' => true]);
});

it('validates processor and provider configuration at construction', function (): void {
    $processors = new AdminFormProcessorConfigFactory();
    $providers = new AdminFormConfigProviderFactory();
    expect(fn () => $processors->create(['processors' => ['example' => ['MissingProcessorClass']]]))
        ->toThrow(InvalidArgumentException::class, 'Admin form processor class does not exist: MissingProcessorClass');
    expect(fn () => $processors->create(['processors' => ['example' => [ConfigFactoryTestProvider::class]]]))
        ->toThrow(InvalidArgumentException::class, 'Admin form processor must implement AdminFormProcessorInterface');
    expect(fn () => $providers->create(['forms' => ['example' => ['MissingProviderClass']]]))
        ->toThrow(InvalidArgumentException::class, 'Admin form provider class does not exist: MissingProviderClass');
    expect(fn () => $providers->create(['forms' => ['example' => [ConfigFactoryTestProcessor::class]]]))
        ->toThrow(InvalidArgumentException::class, 'Admin form provider must implement AdminFormSectionProviderInterface');
    expect(fn () => $processors->create(['processors' => ['wrong' => [ConfigFactoryTestProcessor::class]]]))
        ->toThrow(InvalidArgumentException::class, 'but was registered for wrong');
    expect(fn () => $providers->create(['forms' => ['wrong' => [ConfigFactoryTestProvider::class]]]))
        ->toThrow(InvalidArgumentException::class, 'but was registered for wrong');
});

it('accepts configured and fallback providers after filtering invalid entries', function (): void {
    $factory = new AdminFormConfigProviderFactory();
    ConfigFactoryTestProvider::$instances = 0;
    expect($factory->create(['forms' => ['example' => [null, '', ConfigFactoryTestProvider::class]]])->sectionsFor('example', []))->toBe([]);
    expect(ConfigFactoryTestProvider::$instances)->toBe(1);
    expect($factory->create(['forms' => ['example' => [false, '']]], ['example' => [ConfigFactoryTestProvider::class]])->sectionsFor('example', []))->toBe([]);
    expect(ConfigFactoryTestProvider::$instances)->toBe(2);
});