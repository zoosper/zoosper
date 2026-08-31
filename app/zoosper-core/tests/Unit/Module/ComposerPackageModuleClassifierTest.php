<?php

declare(strict_types=1);

use Zoosper\Core\Module\ComposerPackageModuleClassifier;

it('classifies native Zoosper packages by type alone', function (): void {
    expect(ComposerPackageModuleClassifier::isRuntimeModule([
        'name' => 'acme/blog',
        'type' => 'zoosper-module',
    ]))->toBeTrue();
});

it('privately accepts native and legacy Marko module metadata', function (): void {
    expect(ComposerPackageModuleClassifier::isRuntimeModule([
        'name' => 'marko/example',
        'type' => 'marko-module',
    ]))->toBeTrue();

    expect(ComposerPackageModuleClassifier::isRuntimeModule([
        'name' => 'marko/legacy',
        'type' => 'library',
        'extra' => ['marko' => ['module' => true]],
    ]))->toBeTrue();
});

it('rejects unrelated Composer libraries', function (): void {
    expect(ComposerPackageModuleClassifier::isRuntimeModule([
        'name' => 'acme/library',
        'type' => 'library',
    ]))->toBeFalse();
});










