<?php

declare(strict_types=1);

namespace Zoosper\Grid\Tests\Unit;

use DOMDocument;
use DOMElement;
use Zoosper\Grid\GridFilter;
use Zoosper\Grid\GridFilterOption;
use Zoosper\Grid\GridMultiselectRenderer;

test('multiselect renderer shows names while posting array-valued IDs', function (): void {
    $filter = new GridFilter('site_id', 'Site', 'multiselect', [
        new GridFilterOption('4', 'Main Website'),
        new GridFilterOption('9', 'Wholesale Portal'),
    ]);

    $html = (new GridMultiselectRenderer())->render($filter, ['9']);

    expect($html)->toContain('name="site_id[]"')
        ->toContain('multiple')
        ->toContain('value="4">Main Website</option>')
        ->toContain('value="9" selected>Wholesale Portal</option>');
});

test('multiselect renderer escapes contributed option labels and values', function (): void {
    $hostileValue = '1" onclick="alert(1)';
    $filter = new GridFilter('site_id', 'Site', 'multiselect', [
        new GridFilterOption($hostileValue, '<script>alert(1)</script>'),
    ]);

    $html = (new GridMultiselectRenderer())->render($filter);
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    try {
        $document->loadHTML(
            '<!doctype html><html><body>' . $html . '</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }

    $option = $document->getElementsByTagName('option')->item(0);

    expect($option)->toBeInstanceOf(DOMElement::class);
    expect($option->getAttribute('value'))->toBe($hostileValue);
    expect($option->hasAttribute('onclick'))->toBeFalse();
    expect($option->textContent)->toBe('<script>alert(1)</script>');
    expect($document->getElementsByTagName('script')->length)->toBe(0);
});

test('multiselect renderer rejects any other filter type', function (): void {
    expect(fn (): string => (new GridMultiselectRenderer())->render(
        new GridFilter('site_id', 'Site', 'text'),
    ))->toThrow(\InvalidArgumentException::class, 'requires a multiselect filter');
});
