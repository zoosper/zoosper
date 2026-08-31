<?php

declare(strict_types=1);

use Zoosper\Core\Asset\AssetUrlGenerator;

/*
 * Phase C2 behavioural tests for the new optional cache-busting version
 * parameter on AssetUrlGenerator::url().
 */

it('builds a URL with no query string when no version is given (backward compatible)', function (): void {
    $generator = new AssetUrlGenerator();

    expect($generator->url('zoosper-admin', 'css/zoosper-grid.css'))
        ->toBe('/asset/zoosper-admin/css/zoosper-grid.css');
});

it('appends a ?v= query string when a version is given', function (): void {
    $generator = new AssetUrlGenerator();

    expect($generator->url('zoosper-admin', 'css/zoosper-grid.css', '1.37l'))
        ->toBe('/asset/zoosper-admin/css/zoosper-grid.css?v=1.37l');
});

it('accepts an integer version (e.g. a unix timestamp)', function (): void {
    $generator = new AssetUrlGenerator();

    expect($generator->url('zoosper-admin', 'css/zoosper-grid.css', 1732492800))
        ->toBe('/asset/zoosper-admin/css/zoosper-grid.css?v=1732492800');
});

it('treats an empty-string version the same as no version at all', function (): void {
    $generator = new AssetUrlGenerator();

    expect($generator->url('zoosper-admin', 'css/zoosper-grid.css', ''))
        ->toBe('/asset/zoosper-admin/css/zoosper-grid.css');
});

it('url-encodes a version value that contains special characters', function (): void {
    $generator = new AssetUrlGenerator();

    expect($generator->url('zoosper-admin', 'css/zoosper-grid.css', 'a b&c'))
        ->toBe('/asset/zoosper-admin/css/zoosper-grid.css?v=a%20b%26c');
});










