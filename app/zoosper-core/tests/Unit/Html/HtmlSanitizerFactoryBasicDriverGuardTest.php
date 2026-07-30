<?php

declare(strict_types=1);

use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Html\BasicHtmlSanitizer;
use Zoosper\Core\Html\HtmlPurifierSanitizer;
use Zoosper\Core\Html\HtmlSanitizerFactory;

/**
 * SECURITY REGRESSION TEST — proves HtmlSanitizerFactory refuses to build
 * BasicHtmlSanitizer (the conservative, regex-based fallback) unless
 * explicitly confirmed via 'allow_basic_driver', and proves the default
 * ('htmlpurifier') driver is completely unaffected.
 *
 * NOTE: app/zoosper-core/tests/Unit/Html/WriteTimeSanitizationTest.php
 * constructs `new BasicHtmlSanitizer()` DIRECTLY (not through this
 * factory), specifically because it needs a dependency-free, deterministic
 * sanitizer for its own unrelated regression tests. That file is
 * completely unaffected by this fix — the guard added here only applies
 * to HtmlSanitizerFactory::create(), never to BasicHtmlSanitizer itself
 * (which remains a normal, freely-constructible class, exactly as
 * before).
 *
 * File placement: app/zoosper-core/tests/Unit/Html/HtmlSanitizerFactoryBasicDriverGuardTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
it('refuses to build the basic driver when allow_basic_driver is not explicitly true', function (): void {
    $factory = new HtmlSanitizerFactory(['driver' => 'basic']);

    expect(fn () => $factory->create())
        ->toThrow(ZoosperException::class, 'The basic HTML sanitizer driver requires explicit confirmation');
});

it('refuses to build the basic driver when allow_basic_driver is explicitly false', function (): void {
    $factory = new HtmlSanitizerFactory(['driver' => 'basic', 'allow_basic_driver' => false]);

    expect(fn () => $factory->create())->toThrow(ZoosperException::class);
});

it('builds the basic driver successfully when allow_basic_driver is explicitly true', function (): void {
    $factory = new HtmlSanitizerFactory(['driver' => 'basic', 'allow_basic_driver' => true]);
    $sanitizer = $factory->create();

    expect($sanitizer)->toBeInstanceOf(BasicHtmlSanitizer::class);

    // Confirm it's genuinely functional, not just constructed.
    $result = $sanitizer->sanitise('<p>Hello</p><script>alert(1)</script>');
    expect((string) $result)->toContain('<p>Hello</p>');
    expect((string) $result)->not->toContain('<script>');
});

it('builds the default (htmlpurifier) driver without requiring any confirmation flag', function (): void {
    // The default driver must remain completely unaffected by this fix —
    // no guard, no new required config, exactly as before.
    $factory = new HtmlSanitizerFactory(['driver' => 'htmlpurifier']);
    $sanitizer = $factory->create();

    expect($sanitizer)->toBeInstanceOf(HtmlPurifierSanitizer::class);
});

it('still throws for a genuinely unsupported driver, unrelated to the basic-driver guard', function (): void {
    $factory = new HtmlSanitizerFactory(['driver' => 'not-a-real-driver']);

    expect(fn () => $factory->create())
        ->toThrow(ZoosperException::class, 'Unsupported HTML sanitizer driver: not-a-real-driver');
});
