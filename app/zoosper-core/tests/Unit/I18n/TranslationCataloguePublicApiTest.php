<?php

declare(strict_types=1);

use Zoosper\Core\I18n\TranslationCatalogue;

it('exposes the locale and catalogue entries without changing fallback behavior', function (): void {
    $catalogue = new TranslationCatalogue('fr', ['Hello' => 'Bonjour', 'Empty' => '']);

    expect($catalogue->locale)->toBe('fr')
        ->and($catalogue->has('Hello'))->toBeTrue()
        ->and($catalogue->has('Empty'))->toBeTrue()
        ->and($catalogue->has('Missing'))->toBeFalse()
        ->and($catalogue->get('Missing'))->toBe('Missing')
        ->and($catalogue->messages())->toBe(['Hello' => 'Bonjour', 'Empty' => '']);
});