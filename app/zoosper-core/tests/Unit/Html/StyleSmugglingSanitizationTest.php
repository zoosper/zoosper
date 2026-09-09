<?php

declare(strict_types=1);

use Zoosper\Core\Html\BasicHtmlSanitizer;

it('removes style elements and inline style attributes from editor controlled html', function (): void {
    $html = '<style>body{background:url(https://attacker.test/pixel)}</style>'
        . '<p style="background:url(https://attacker.test/pixel);position:fixed">Safe text</p>';

    $clean = (new BasicHtmlSanitizer())->sanitise($html)->toString();

    expect($clean)
        ->not->toContain('<style')
        ->not->toContain('style=')
        ->not->toContain('attacker.test')
        ->toContain('<p>Safe text</p>');
});

it('neutralises active and data uri schemes in quoted href and src attributes', function (string $attribute, string $scheme): void {
    $html = sprintf('<a %s="%s:text/html,unsafe">Link</a>', $attribute, $scheme);
    $clean = (new BasicHtmlSanitizer())->sanitise($html)->toString();

    expect(strtolower($clean))
        ->not->toContain($scheme . ':')
        ->toContain($attribute . '="#"');
})->with([
    ['href', 'javascript'],
    ['href', 'vbscript'],
    ['href', 'data'],
    ['src', 'javascript'],
    ['src', 'data'],
]);

it('keeps the production purifier allowlist free of style execution surfaces', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Html/HtmlPurifierSanitizer.php');

    expect($source)
        ->toContain("\$config->set('HTML.Allowed'")
        ->not->toContain('style]')
        ->not->toContain('style|')
        ->not->toContain('CSS.AllowedProperties');
});
