<?php

declare(strict_types=1);

use Zoosper\AdminGrid\BulkAction\GridBulkConfirmationGuard;
use Zoosper\AdminGrid\BulkAction\GridBulkHttpRequest;
use Zoosper\AdminGrid\BulkAction\GridBulkHttpRequestParser;
use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkConfirmationPolicy;
use Zoosper\Grid\BulkAction\GridBulkExecutionType;
use Zoosper\Grid\BulkAction\GridBulkSelectionScope;

it('parses only POST forms with an identity array', function (): void {
    $parser = new GridBulkHttpRequestParser();
    $request = $parser->parse('admin.pages', new GridBulkHttpRequest('POST', [
        'bulk_action' => 'page.publish',
        'selected_ids' => ['3', '2'],
    ]));
    expect($request->gridKey)->toBe('admin.pages')
        ->and($request->actionId)->toBe('page.publish')
        ->and($request->selectedIdentities)->toBe(['3', '2']);

    expect(fn () => $parser->parse('admin.pages', new GridBulkHttpRequest('GET', [])))
        ->toThrow(InvalidArgumentException::class, 'require POST');
    expect(fn () => $parser->parse('admin.pages', new GridBulkHttpRequest('POST', [
        'bulk_action' => 'page.publish', 'selected_ids' => '3',
    ])))->toThrow(InvalidArgumentException::class, 'must be an array');
});

it('requires action-bound confirmation for mutating definitions', function (): void {
    $definition = new GridBulkActionDefinition(
        'page.publish', 'Publish', GridBulkSelectionScope::EXPLICIT_IDENTITIES,
        GridBulkExecutionType::SERVER_MUTATION, GridBulkConfirmationPolicy::CONFIRM,
    );
    $guard = new GridBulkConfirmationGuard();
    expect(fn () => $guard->assertConfirmed($definition, []))
        ->toThrow(InvalidArgumentException::class, 'explicit confirmation');
    expect(fn () => $guard->assertConfirmed($definition, ['confirmed_action' => 'page.delete']))
        ->toThrow(InvalidArgumentException::class, 'explicit confirmation');
    $guard->assertConfirmed($definition, ['confirmed_action' => 'page.publish']);
    expect(true)->toBeTrue();
});











