<?php

declare(strict_types=1);

use Zoosper\AdminForm\AdminFormDefinition;
use Zoosper\AdminForm\AdminFormField;
use Zoosper\AdminForm\AdminFormRenderer;

it('preserves zero-like descriptions and errors while requiring an explicit boolean flag', function (): void {
    $form = new AdminFormDefinition(
        handle: 'admin.scalar-boundary',
        fields: [
            new AdminFormField('title', 'text', 'Title', 10, 'general', ['required' => true]),
            new AdminFormField('optional', 'text', 'Optional', 20, 'general', ['required' => 1]),
        ],
        sections: ['general' => ['title' => 'General', 'description' => '0']],
    );

    $html = (new AdminFormRenderer())->render($form, errors: ['title' => '0']);

    expect($html)->toContain('<p class="muted">0</p>')
        ->and($html)->toContain('<div class="field-error">0</div>')
        ->and(substr_count($html, 'has-error'))->toBe(1)
        ->and(substr_count($html, ' required'))->toBe(1);
});

it('omits empty descriptions and empty errors', function (): void {
    $form = new AdminFormDefinition(
        handle: 'admin.empty-boundary',
        fields: [new AdminFormField('title', 'text', 'Title', 10, 'general')],
        sections: ['general' => ['title' => 'General', 'description' => '']],
    );

    $html = (new AdminFormRenderer())->render($form, errors: ['title' => '']);

    expect($html)->not->toContain('<p class="muted">')
        ->and($html)->not->toContain('field-error')
        ->and($html)->not->toContain('has-error');
});
