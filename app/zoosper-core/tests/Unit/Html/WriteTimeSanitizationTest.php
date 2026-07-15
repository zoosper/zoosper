<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Html;

/**
 * Regression tests for write-time HTML sanitisation.
 *
 * Phase 1.21 - First regression suite (co-located in zoosper-core).
 *
 * Preserves the security invariant that CMS-managed rich content is sanitised
 * before it is stored. These tests use BasicHtmlSanitizer because it is
 * dependency-free and deterministic; it shares the HtmlSanitizerInterface
 * contract (sanitise(): SanitizedHtml) with the production HtmlPurifierSanitizer.
 *
 * PCI-aware: the sanitiser and SanitizedHtml are for CMS body HTML only - never
 * for secrets, tokens, or payment data.
 */

use Zoosper\Core\Html\BasicHtmlSanitizer;
use Zoosper\Core\Html\HtmlSanitizerInterface;
use Zoosper\Core\Html\SanitizedHtml;

test('script tags are stripped and safe markup is preserved', function () {
    // Arrange
    $sanitizer = new BasicHtmlSanitizer();
    expect($sanitizer)->toBeInstanceOf(HtmlSanitizerInterface::class);

    // Act
    $result = $sanitizer->sanitise('<p>Hello</p><script>alert(1)</script>');

    // Assert
    expect($result)->toBeInstanceOf(SanitizedHtml::class);
    $html = (string) $result;
    expect($html)->toContain('<p>Hello</p>');
    expect($html)->not->toContain('<script>');
    expect($html)->not->toContain('alert(1)');
});

test('inline event-handler attributes are stripped', function () {
    // Arrange
    $sanitizer = new BasicHtmlSanitizer();

    // Act - use the explicit toString() accessor here.
    $html = $sanitizer->sanitise('<img src="x" onerror="alert(1)">')->toString();

    // Assert - the onerror handler must not survive.
    expect($html)->not->toContain('onerror');
    expect($html)->not->toContain('alert(1)');
});

test('javascript scheme hrefs are neutralised', function () {
    // Arrange
    $sanitizer = new BasicHtmlSanitizer();

    // Act
    $html = (string) $sanitizer->sanitise('<a href="javascript:evil()">x</a>');

    // Assert
    expect($html)->not->toContain('javascript:');
});
